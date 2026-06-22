import os
import sys
import argparse
import math
import numpy as np
import pandas as pd
import yfinance as yf
from flask import Flask, jsonify, request, render_template

# Initialize Flask application
# Set template folder to templates
app = Flask(__name__, template_folder='templates')

# Manual CORS config to support all browsers and requests safely
@app.after_request
def after_request(response):
    response.headers.add('Access-Control-Allow-Origin', '*')
    response.headers.add('Access-Control-Allow-Headers', 'Content-Type,Authorization')
    response.headers.add('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE,OPTIONS')
    return response

def analyze_ticker_data(ticker_symbol):
    """
    Fetches stock data using yfinance and runs the rule-based investment analysis.
    """
    print(f"Fetching data for: {ticker_symbol}")
    
    # 1. Fetch historical data (last 1 year of daily records)
    ticker = yf.Ticker(ticker_symbol)
    df = ticker.history(period="1y")
    
    if df.empty:
        raise ValueError(f"No stock data could be found for ticker symbol '{ticker_symbol}'. Please verify.")
    
    # Fetch ticker information safely (falling back if info dictionary is restricted/empty)
    try:
        info = ticker.info
        company_name = info.get('longName') or info.get('shortName') or ticker_symbol
        sector = info.get('sector') or 'Financial Systems / General Market'
        current_price = info.get('currentPrice') or info.get('regularMarketPrice') or float(df['Close'].iloc[-1])
        pe_ratio = info.get('trailingPE')
    except Exception:
        company_name = ticker_symbol
        sector = 'General Market Equity'
        current_price = float(df['Close'].iloc[-1])
        pe_ratio = None
    
    # Clean data structure
    df = df.reset_index()
    # Format Date column
    df['DateStr'] = df['Date'].dt.strftime('%Y-%m-%d')
    
    # 2. Compute Technical Metrics
    # A. Simple Moving Averages
    df['SMA20'] = df['Close'].rolling(window=20).mean()
    df['SMA50'] = df['Close'].rolling(window=50).mean()
    
    # B. Relative Strength Index (RSI - 14 Days)
    delta = df['Close'].diff()
    gain = (delta.where(delta > 0, 0)).rolling(window=14).mean()
    loss = (-delta.where(delta < 0, 0)).rolling(window=14).mean()
    # Avoid division by zero
    loss = loss.replace(0, 0.00001)
    rs = gain / loss
    df['RSI'] = 100 - (100 / (1 + rs))
    
    # C. MACD (12, 26, 9 EMA)
    ema12 = df['Close'].ewm(span=12, adjust=False).mean()
    ema26 = df['Close'].ewm(span=26, adjust=False).mean()
    df['MACD'] = ema12 - ema26
    df['MACD_Signal'] = df['MACD'].ewm(span=9, adjust=False).mean()
    
    # D. Volume 20-day Average
    df['Vol_Avg20'] = df['Volume'].rolling(window=20).mean()
    
    # Grab final row indices (current values)
    latest_row = df.iloc[-1]
    
    # Extract latest indicators
    close_val = float(latest_row['Close'])
    open_val = float(latest_row['Open'])
    high_val = float(latest_row['High'])
    low_val = float(latest_row['Low'])
    volume_val = int(latest_row['Volume'])
    
    sma20_val = float(latest_row['SMA20']) if not pd.isna(latest_row['SMA20']) else None
    sma50_val = float(latest_row['SMA50']) if not pd.isna(latest_row['SMA50']) else None
    rsi_val = float(latest_row['RSI']) if not pd.isna(latest_row['RSI']) else None
    macd_val = float(latest_row['MACD']) if not pd.isna(latest_row['MACD']) else None
    macd_sig_val = float(latest_row['MACD_Signal']) if not pd.isna(latest_row['MACD_Signal']) else None
    vol_avg_val = float(latest_row['Vol_Avg20']) if not pd.isna(latest_row['Vol_Avg20']) else None
    
    # If there's insufficient history (e.g. newly listed stock)
    if sma20_val is None or rsi_val is None or macd_val is None:
        raise ValueError("Insufficient historical pricing data to calculate full technical indicators.")
    
    # 3. Rule-Based Algorithm & Decision System
    rules_triggered = []
    total_checks = 5
    score = 0
    
    # Rule 1: Short-Term Trend Alignment
    is_above_sma20 = close_val > sma20_val
    if is_above_sma20:
        score += 1
        rules_triggered.append({
            "name": "Short-Term Trend Alignment",
            "status": "BULLISH",
            "detail": f"Stock price (₹{close_val:.2f}) is trading above its 20-day moving average (₹{sma20_val:.2f}), confirming positive short-term momentum.",
            "value": f"Price > SMA20"
        })
    else:
        rules_triggered.append({
            "name": "Short-Term Trend Alignment",
            "status": "BEARISH",
            "detail": f"Stock price (₹{close_val:.2f}) is below its 20-day moving average (₹{sma20_val:.2f}), showing short-term weakness.",
            "value": f"Price < SMA20"
        })
        
    # Rule 2: Medium-Term Trend Strength
    is_golden_trend = sma20_val > sma50_val
    if is_golden_trend:
        score += 1
        rules_triggered.append({
            "name": "Medium-Term Trend Alignment",
            "status": "BULLISH",
            "detail": f"The 20-day moving average (₹{sma20_val:.2f}) is above the 50-day moving average (₹{sma50_val:.2f}), indicating a healthy intermediate-term uptrend.",
            "value": f"SMA20 > SMA50"
        })
    else:
        rules_triggered.append({
            "name": "Medium-Term Trend Alignment",
            "status": "BEARISH",
            "detail": f"The 20-day moving average (₹{sma20_val:.2f}) sits below the 50-day moving average (₹{sma50_val:.2f}), displaying intermediate-term downward pressure.",
            "value": f"SMA20 < SMA50"
        })
        
    # Rule 3: RSI Momentum check
    if rsi_val <= 32:
        score += 1
        rules_triggered.append({
            "name": "Relative Strength Index (RSI)",
            "status": "BULLISH (OVERSOLD)",
            "detail": f"RSI is extremely low at {rsi_val:.1f}, indicating the stock is oversold. This historically marks accumulation zones and trend reversals.",
            "value": f"RSI = {rsi_val:.1f} (<= 32)"
        })
    elif rsi_val >= 68:
        rules_triggered.append({
            "name": "Relative Strength Index (RSI)",
            "status": "BEARISH (OVERBOUGHT)",
            "detail": f"RSI is high at {rsi_val:.1f}, indicating the asset is overbought. Risk of short-term price exhaustion or profit-taking is high.",
            "value": f"RSI = {rsi_val:.1f} (>= 68)"
        })
    else:
        # Stable neutral to slightly bullish if RSI is healthy and above neutral line
        is_healthy_rsi = rsi_val > 45
        if is_healthy_rsi:
            score += 1
        rules_triggered.append({
            "name": "Relative Strength Index (RSI)",
            "status": "BULLISH" if is_healthy_rsi else "NEUTRAL",
            "detail": f"RSI is at a stable level of {rsi_val:.1f}. This indicates a sustainable momentum cycle with room to grow.",
            "value": f"RSI = {rsi_val:.1f} (Healthy Range)"
        })
        
    # Rule 4: MACD Signal crossover
    is_macd_bullish = macd_val > macd_sig_val
    if is_macd_bullish:
        score += 1
        rules_triggered.append({
            "name": "Moving Average Convergence Divergence (MACD)",
            "status": "BULLISH",
            "detail": f"The MACD line ({macd_val:.3f}) is trading above its signal line ({macd_sig_val:.3f}), representing an active buying crossover signal.",
            "value": f"MACD > Signal"
        })
    else:
        rules_triggered.append({
            "name": "Moving Average Convergence Divergence (MACD)",
            "status": "BEARISH",
            "detail": f"The MACD line ({macd_val:.3f}) is below its signal line ({macd_sig_val:.3f}), showing an active selling or momentum slowdown signal.",
            "value": f"MACD < Signal"
        })
        
    # Rule 5: Volume strength verification
    is_volume_support = volume_val > vol_avg_val
    if is_volume_support:
        score += 1
        rules_triggered.append({
            "name": "Volume Support Check",
            "status": "BULLISH",
            "detail": f"Current volume ({volume_val:,}) exceeds the 20-day average volume ({int(vol_avg_val):,}), showing strong participation support.",
            "value": f"Volume > 20-Day Avg"
        })
    else:
        rules_triggered.append({
            "name": "Volume Support Check",
            "status": "NEUTRAL",
            "detail": f"Current volume ({volume_val:,}) is below the 20-day average volume ({int(vol_avg_val):,}), suggesting low retail or institutional interest.",
            "value": f"Volume < 20-Day Avg"
        })
        
    # Decide recommendation based on final score
    verdict = "INVEST" if score >= 3 else "AVOID"
    confidence = int((score / total_checks) * 100)
    
    # 4. Prepare historical data table (last 15 rows for display)
    history_subset = df.tail(15).copy()
    raw_history_list = []
    for _, r in history_subset.iterrows():
        raw_history_list.append({
            "date": r['DateStr'],
            "open": float(r['Open']),
            "high": float(r['High']),
            "low": float(r['Low']),
            "close": float(r['Close']),
            "volume": int(r['Volume']),
            "sma20": float(r['SMA20']) if not pd.isna(r['SMA20']) else None,
            "rsi": float(r['RSI']) if not pd.isna(r['RSI']) else None
        })
        
    # 5. Build response payload
    payload = {
        "ticker": ticker_symbol,
        "company_name": company_name,
        "sector": sector,
        "current_price": current_price,
        "pe_ratio": pe_ratio if pe_ratio and not math.isnan(pe_ratio) else "N/A",
        "open": open_val,
        "high": high_val,
        "low": low_val,
        "volume": volume_val,
        "sma20": sma20_val,
        "sma50": sma50_val,
        "rsi": rsi_val,
        "macd": macd_val,
        "macd_signal": macd_sig_val,
        "volume_avg20": vol_avg_val,
        "verdict": verdict,
        "confidence_score": confidence,
        "score": score,
        "rules_breakdown": rules_triggered,
        "history": raw_history_list
    }
    
    return payload

@app.route('/')
def home():
    """Serves the primary UI dashboard template."""
    return render_template('index.html')

@app.route('/api/analyze')
def api_analyze():
    """Exposes analysis engine results via a JSON API endpoint."""
    ticker_symbol = request.args.get('ticker', '').strip().upper()
    
    if not ticker_symbol:
        return jsonify({"error": "Ticker query parameter 'ticker' is required."}), 400
        
    try:
        data = analyze_ticker_data(ticker_symbol)
        return jsonify(data)
    except Exception as e:
        import traceback
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description="Stock Market Investment Analysis Server")
    parser.add_argument('--test', type=str, help='Test ticker symbol from CLI (e.g. INFY.NS) and exit.')
    parser.add_argument('--port', type=int, default=5001, help='Port to run Flask server on.')
    
    args = parser.parse_args()
    
    if args.test:
        try:
            results = analyze_ticker_data(args.test)
            print("\n============ CLI TEST RESULTS ============")
            print(f"Ticker: {results['ticker']} ({results['company_name']})")
            print(f"Sector: {results['sector']}")
            print(f"Price: INR {results['current_price']:.2f} | P/E: {results['pe_ratio']}")
            print(f"Recommendation Verdict: {results['verdict']} (Confidence: {results['confidence_score']}%)")
            print("\nRules Triggered Breakdown:")
            for rule in results['rules_breakdown']:
                # Clean up Unicode characters for console output safely
                safe_detail = rule['detail'].replace('₹', 'INR ')
                print(f" - [{rule['status']}] {rule['name']}: {safe_detail}")
            print("===========================================")
            sys.exit(0)
        except Exception as ex:
            print(f"Error testing ticker analysis: {ex}")
            sys.exit(1)
            
    # Run the Flask app on standard port 5001
    print("Starting accessible analysis engine server at http://127.0.0.1:5001...")
    app.run(debug=True, host='0.0.0.0', port=args.port)
