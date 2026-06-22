import sys
import mysql.connector
import yfinance as yf
import pandas as pd
from datetime import datetime

# Database config
db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': 'root',
    'database': 'stock_tracker_db'
}

def connect_db():
    try:
        conn = mysql.connector.connect(**db_config)
        return conn
    except Exception as e:
        print(f"Error connecting to database: {e}")
        sys.exit(1)

def collect_historical_data():
    conn = connect_db()
    cursor = conn.cursor(dictionary=True)
    
    # 1. Fetch supported stocks from database
    cursor.execute("SELECT id, symbol, company_name FROM stocks")
    stocks = cursor.fetchall()
    
    if not stocks:
        print("No supported stocks found in database. Please run seed data first.")
        conn.close()
        return

    print(f"Found {len(stocks)} stocks to process.")
    
    for stock in stocks:
        stock_id = stock['id']
        symbol = stock['symbol']
        company_name = stock['company_name']
        
        print(f"\nProcessing {company_name} ({symbol})...")
        
        try:
            # 2. Download 5 years of daily historical data using yfinance
            print(f"Downloading 5 years of daily historical data from Yahoo Finance for {symbol}...")
            ticker = yf.Ticker(symbol)
            df = ticker.history(period="5y")
            
            if df.empty:
                print(f"Warning: No data returned for symbol {symbol}")
                continue
                
            print(f"Downloaded {len(df)} rows of data.")
            
            # Reset index to get Date column
            df = df.reset_index()
            
            # Prepare batch insert statements
            insert_query = """
                INSERT INTO stock_history (stock_id, date, open, high, low, close, volume)
                VALUES (%s, %s, %s, %s, %s, %s, %s)
                ON DUPLICATE KEY UPDATE
                    open = VALUES(open),
                    high = VALUES(high),
                    low = VALUES(low),
                    close = VALUES(close),
                    volume = VALUES(volume)
            """
            
            batch_data = []
            for _, row in df.iterrows():
                # Extract date in YYYY-MM-DD format
                # Handle tz-aware or tz-naive Timestamp
                date_val = row['Date']
                if hasattr(date_val, 'date'):
                    date_str = date_val.date().strftime('%Y-%m-%d')
                else:
                    date_str = pd.to_datetime(date_val).strftime('%Y-%m-%d')
                
                # Check for NaN and substitute or cast
                open_val = float(row['Open'])
                high_val = float(row['High'])
                low_val = float(row['Low'])
                close_val = float(row['Close'])
                volume_val = int(row['Volume'])
                
                batch_data.append((
                    stock_id,
                    date_str,
                    open_val,
                    high_val,
                    low_val,
                    close_val,
                    volume_val
                ))
            
            # 3. Upsert data in batches
            batch_size = 500
            inserted_count = 0
            for i in range(0, len(batch_data), batch_size):
                chunk = batch_data[i:i+batch_size]
                cursor.executemany(insert_query, chunk)
                inserted_count += len(chunk)
                
            conn.commit()
            print(f"Successfully saved {inserted_count} historical data points for {symbol}.")
            
            # Record admin log
            log_query = "INSERT INTO logs (action, details) VALUES (%s, %s)"
            cursor.execute(log_query, (
                "DATA_COLLECTION", 
                f"Successfully ingested/updated {inserted_count} historical price points for {symbol}."
            ))
            conn.commit()
            
        except Exception as ex:
            print(f"Error processing stock {symbol}: {ex}")
            conn.rollback()
            
    cursor.close()
    conn.close()
    print("\nData collection job completed!")

if __name__ == "__main__":
    collect_historical_data()
