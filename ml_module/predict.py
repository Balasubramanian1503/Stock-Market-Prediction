import sys
import argparse
import mysql.connector
import numpy as np
import pandas as pd
from sklearn.linear_model import LinearRegression
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
from models import NumpyLSTM, create_regression_features

# Database config
db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': 'root',
    'database': 'stock_tracker_db'
}

def connect_db():
    return mysql.connector.connect(**db_config)

def train_and_predict(symbol, train_mode=True):
    conn = connect_db()
    cursor = conn.cursor(dictionary=True)
    
    # 1. Fetch stock metadata
    cursor.execute("SELECT id, symbol, company_name FROM stocks WHERE symbol = %s", (symbol,))
    stock = cursor.fetchone()
    if not stock:
        print(f"Error: Stock symbol {symbol} not found in database.")
        conn.close()
        return False
        
    stock_id = stock['id']
    print(f"\n===========================================")
    print(f"Training & Predicting for {stock['company_name']} ({symbol})")
    print(f"===========================================")
    
    # 2. Load historical stock data
    query = """
        SELECT date, close 
        FROM stock_history 
        WHERE stock_id = %s 
        ORDER BY date ASC
    """
    cursor.execute(query, (stock_id,))
    history = cursor.fetchall()
    
    if len(history) < 50:
        print(f"Warning: Insufficient historical data ({len(history)} rows) for {symbol}. Minimum 50 required.")
        conn.close()
        return False
        
    df = pd.DataFrame(history)
    df['close'] = df['close'].astype(float)
    df['date'] = pd.to_datetime(df['date'])
    
    prices = df['close'].values
    dates = df['date'].values
    
    # Normalize data using MinMax Scaling
    min_val = np.min(prices)
    max_val = np.max(prices)
    denom = (max_val - min_val) if max_val != min_val else 1.0
    scaled_prices = (prices - min_val) / denom
    
    # ==========================================
    # A. LINEAR REGRESSION FORECASTING
    # ==========================================
    print("Training Linear Regression model...")
    X_reg, y_reg = create_regression_features(scaled_prices, window_size=5)
    
    # Split validation set (last 50 days) for metrics computation
    split_idx = len(X_reg) - 50
    X_train_reg, X_val_reg = X_reg[:split_idx], X_reg[split_idx:]
    y_train_reg, y_val_reg = y_reg[:split_idx], y_reg[split_idx:]
    
    reg_model = LinearRegression()
    reg_model.fit(X_train_reg, y_train_reg)
    
    # Validate on val set
    y_pred_val_reg = reg_model.predict(X_val_reg)
    
    # Denormalize validation predictions & actuals
    y_val_unscaled = y_val_reg * denom + min_val
    y_pred_val_unscaled_reg = y_pred_val_reg * denom + min_val
    
    # Compute metrics
    mae_reg = float(mean_absolute_error(y_val_unscaled, y_pred_val_unscaled_reg))
    rmse_reg = float(np.sqrt(mean_squared_error(y_val_unscaled, y_pred_val_unscaled_reg)))
    r2_reg = float(r2_score(y_val_unscaled, y_pred_val_unscaled_reg))
    
    # Compute MAPE-based accuracy
    mape_reg = np.mean(np.abs((y_val_unscaled - y_pred_val_unscaled_reg) / y_val_unscaled))
    accuracy_score_reg = max(10.0, min(99.0, 100.0 * (1.0 - mape_reg)))
    
    # Forecast recursively up to 30 days ahead
    current_window = list(scaled_prices[-5:])
    reg_forecasts = []
    for _ in range(30):
        pred = reg_model.predict([current_window])[0]
        reg_forecasts.append(pred)
        current_window.pop(0)
        current_window.append(pred)
        
    # Unscale forecasts
    reg_forecasts_unscaled = [float(f * denom + min_val) for f in reg_forecasts]
    
    # ==========================================
    # B. LSTM NEURAL NETWORK FORECASTING
    # ==========================================
    print("Training custom NumPy LSTM Neural Network...")
    seq_len = 10
    X_lstm, y_lstm = [], []
    for i in range(len(scaled_prices) - seq_len):
        X_lstm.append(scaled_prices[i : i + seq_len])
        y_lstm.append(scaled_prices[i + seq_len])
    X_lstm = np.array(X_lstm).reshape(-1, seq_len, 1)
    y_lstm = np.array(y_lstm).reshape(-1, 1)
    
    # Validation split
    split_idx_lstm = len(X_lstm) - 50
    X_train_lstm, X_val_lstm = X_lstm[:split_idx_lstm], X_lstm[split_idx_lstm:]
    y_train_lstm, y_val_lstm = y_lstm[:split_idx_lstm], y_lstm[split_idx_lstm:]
    
    # Instantiate and fit
    lstm_model = NumpyLSTM(input_dim=1, hidden_dim=16, output_dim=1, lr=0.01)
    
    # Train on the last 500 rows to optimize training time & maintain high responsiveness
    train_len = min(500, len(X_train_lstm))
    lstm_model.fit(X_train_lstm[-train_len:], y_train_lstm[-train_len:], epochs=15)
    
    # Validate on val set
    y_pred_val_lstm = []
    for i in range(len(X_val_lstm)):
        pred, _ = lstm_model.forward(X_val_lstm[i])
        y_pred_val_lstm.append(pred[0, 0])
    y_pred_val_lstm = np.array(y_pred_val_lstm)
    
    # Denormalize validation predictions & actuals
    y_val_unscaled_lstm = y_val_lstm.flatten() * denom + min_val
    y_pred_val_unscaled_lstm = y_pred_val_lstm * denom + min_val
    
    # Compute metrics
    mae_lstm = float(mean_absolute_error(y_val_unscaled_lstm, y_pred_val_unscaled_lstm))
    rmse_lstm = float(np.sqrt(mean_squared_error(y_val_unscaled_lstm, y_pred_val_unscaled_lstm)))
    r2_lstm = float(r2_score(y_val_unscaled_lstm, y_pred_val_unscaled_lstm))
    
    # Compute MAPE-based accuracy
    mape_lstm = np.mean(np.abs((y_val_unscaled_lstm - y_pred_val_unscaled_lstm) / y_val_unscaled_lstm))
    accuracy_score_lstm = max(10.0, min(99.0, 100.0 * (1.0 - mape_lstm)))
    
    # Forecast recursively up to 30 days ahead
    current_seq = scaled_prices[-10:].reshape(-1, 1)
    lstm_forecasts = []
    for _ in range(30):
        pred, _ = lstm_model.forward(current_seq)
        pred_val = float(pred[0, 0])
        lstm_forecasts.append(pred_val)
        current_seq = np.vstack((current_seq[1:], [[pred_val]]))
        
    # Unscale forecasts
    lstm_forecasts_unscaled = [float(f * denom + min_val) for f in lstm_forecasts]
    
    # ==========================================
    # C. SAVE TO DATABASE (predictions table)
    # ==========================================
    # Target dates
    last_date = pd.to_datetime(dates[-1])
    target_dates = []
    
    # Generate next 30 calendar days (excluding weekends or standard prediction horizons)
    curr_date = last_date
    for _ in range(30):
        curr_date += pd.Timedelta(days=1)
        # Settle dates
        target_dates.append(curr_date.strftime('%Y-%m-%d'))
        
    # Predictions structure to save
    # Predict: Tomorrow (index 0), Next Week / 7 Days (index 6), Next Month / 30 Days (index 29)
    save_horizons = [0, 6, 29]
    
    # Delete old predictions for this stock before adding new ones
    cursor.execute("DELETE FROM predictions WHERE stock_id = %s", (stock_id,))
    conn.commit()
    
    # Insert Linear Regression Predictions
    query_insert = """
        INSERT INTO predictions (stock_id, model_type, target_date, predicted_price, mae, rmse, r2_score, confidence_score)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
    """
    
    for h in save_horizons:
        cursor.execute(query_insert, (
            stock_id,
            'linear_regression',
            target_dates[h],
            reg_forecasts_unscaled[h],
            mae_reg,
            rmse_reg,
            r2_reg,
            accuracy_score_reg
        ))
        
    # Insert LSTM Predictions
    for h in save_horizons:
        cursor.execute(query_insert, (
            stock_id,
            'lstm',
            target_dates[h],
            lstm_forecasts_unscaled[h],
            mae_lstm,
            rmse_lstm,
            r2_lstm,
            accuracy_score_lstm
        ))
        
    conn.commit()
    
    # Insert admin logs
    log_query = "INSERT INTO logs (action, details) VALUES (%s, %s)"
    cursor.execute(log_query, (
        "MODEL_TRAINING",
        f"Retrained models for {symbol}. LR Confidence: {accuracy_score_reg:.2f}%. LSTM Confidence: {accuracy_score_lstm:.2f}%."
    ))
    conn.commit()
    
    print("\nMetrics Summary:")
    print(f"Linear Regression | MAE: {mae_reg:.2f} | RMSE: {rmse_reg:.2f} | R²: {r2_reg:.4f} | Confidence: {accuracy_score_reg:.2f}%")
    print(f"LSTM Network      | MAE: {mae_lstm:.2f} | RMSE: {rmse_lstm:.2f} | R²: {r2_lstm:.4f} | Confidence: {accuracy_score_lstm:.2f}%")
    print(f"Forecast Tomorrow (LR):   INR {reg_forecasts_unscaled[0]:.2f}")
    print(f"Forecast Tomorrow (LSTM): INR {lstm_forecasts_unscaled[0]:.2f}")
    print("Predictions saved successfully!")
    
    cursor.close()
    conn.close()
    return True

def process_all_stocks():
    conn = connect_db()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT symbol FROM stocks")
    stocks = cursor.fetchall()
    cursor.close()
    conn.close()
    
    print(f"Found {len(stocks)} stocks to retrain...")
    success_count = 0
    for stock in stocks:
        if train_and_predict(stock['symbol']):
            success_count += 1
            
    print(f"\nBatch training complete! Successfully retrained {success_count}/{len(stocks)} models.")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Predictive Analytics Machine Learning Module")
    parser.add_argument('--stock', type=str, help='Stock symbol to retrain and predict (e.g. RELIANCE.NS)')
    parser.add_argument('--all', action='store_true', help='Retrain and predict all supported stocks')
    
    args = parser.parse_args()
    
    if args.all:
        process_all_stocks()
    elif args.stock:
        train_and_predict(args.stock)
    else:
        parser.print_help()
