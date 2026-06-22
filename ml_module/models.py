import numpy as np

# ==========================================
# 1. Custom Vectorized NumPy LSTM Class
# ==========================================
class NumpyLSTM:
    def __init__(self, input_dim=1, hidden_dim=16, output_dim=1, lr=0.01):
        self.input_dim = input_dim
        self.hidden_dim = hidden_dim
        self.output_dim = output_dim
        self.lr = lr
        
        # Concatenated dimensions for gate calculations: [hidden + input]
        concat_dim = hidden_dim + input_dim
        
        # Initialize weights with Xavier/Glorot initialization
        self.Wf = np.random.randn(hidden_dim, concat_dim) * np.sqrt(2.0 / (hidden_dim + concat_dim))
        self.Wi = np.random.randn(hidden_dim, concat_dim) * np.sqrt(2.0 / (hidden_dim + concat_dim))
        self.Wc = np.random.randn(hidden_dim, concat_dim) * np.sqrt(2.0 / (hidden_dim + concat_dim))
        self.Wo = np.random.randn(hidden_dim, concat_dim) * np.sqrt(2.0 / (hidden_dim + concat_dim))
        
        self.bf = np.zeros((hidden_dim, 1))
        self.bi = np.zeros((hidden_dim, 1))
        self.bc = np.zeros((hidden_dim, 1))
        self.bo = np.zeros((hidden_dim, 1))
        
        # Output layer weights
        self.Wy = np.random.randn(output_dim, hidden_dim) * np.sqrt(2.0 / (output_dim + hidden_dim))
        self.by = np.zeros((output_dim, 1))

    def sigmoid(self, x):
        return 1.0 / (1.0 + np.exp(-np.clip(x, -500, 500)))

    def sigmoid_derivative(self, s):
        return s * (1.0 - s)

    def tanh(self, x):
        return np.tanh(x)

    def tanh_derivative(self, t):
        return 1.0 - t ** 2

    def forward_step(self, x_t, h_prev, c_prev):
        # x_t is (input_dim, 1), h_prev is (hidden_dim, 1), c_prev is (hidden_dim, 1)
        concat = np.vstack((h_prev, x_t)) # shape: (concat_dim, 1)
        
        # Gates calculations
        f_t = self.sigmoid(np.dot(self.Wf, concat) + self.bf) # forget gate
        i_t = self.sigmoid(np.dot(self.Wi, concat) + self.bi) # input gate
        c_tilde = self.tanh(np.dot(self.Wc, concat) + self.bc) # candidate state
        
        # Cell state update
        c_t = f_t * c_prev + i_t * c_tilde
        
        # Output gate and Hidden state
        o_t = self.sigmoid(np.dot(self.Wo, concat) + self.bo)
        h_t = o_t * self.tanh(c_t)
        
        return h_t, c_t, f_t, i_t, c_tilde, o_t, concat

    def forward(self, X_seq):
        # X_seq: sequence of shape (seq_len, input_dim)
        seq_len = X_seq.shape[0]
        
        h = {}
        c = {}
        f = {}
        i = {}
        c_tilde = {}
        o = {}
        concat = {}
        
        # Initial hidden and cell states
        h[-1] = np.zeros((self.hidden_dim, 1))
        c[-1] = np.zeros((self.hidden_dim, 1))
        
        for t in range(seq_len):
            x_t = X_seq[t].reshape(-1, 1)
            h_t, c_t, f_t, i_t, c_tilde_t, o_t, concat_t = self.forward_step(x_t, h[-1 if t==0 else t-1], c[-1 if t==0 else t-1])
            
            h[t] = h_t
            c[t] = c_t
            f[t] = f_t
            i[t] = i_t
            c_tilde[t] = c_tilde_t
            o[t] = o_t
            concat[t] = concat_t
            
        # Final output layer prediction
        y_pred = np.dot(self.Wy, h[seq_len - 1]) + self.by
        
        cache = (h, c, f, i, c_tilde, o, concat, X_seq)
        return y_pred, cache

    def backward(self, d_out, cache):
        h, c, f, i, c_tilde, o, concat, X_seq = cache
        seq_len = X_seq.shape[0]
        
        # Gradients initialization
        dWf, dWi, dWc, dWo = np.zeros_like(self.Wf), np.zeros_like(self.Wi), np.zeros_like(self.Wc), np.zeros_like(self.Wo)
        dbf, dbi, dbc, dbo = np.zeros_like(self.bf), np.zeros_like(self.bi), np.zeros_like(self.bc), np.zeros_like(self.bo)
        dWy = np.dot(d_out, h[seq_len - 1].T)
        dby = d_out.copy()
        
        # Backprop through time
        dh_next = np.dot(self.Wy.T, d_out)
        dc_next = np.zeros((self.hidden_dim, 1))
        
        for t in reversed(range(seq_len)):
            # Gradients from hidden state
            dh = dh_next
            
            # Gradient of output gate
            do = dh * self.tanh(c[t])
            do_raw = self.sigmoid_derivative(o[t]) * do
            
            # Gradient of cell state
            dc = dh * o[t] * self.tanh_derivative(self.tanh(c[t])) + dc_next
            
            # Gradients of gates and candidates
            dc_tilde = dc * i[t]
            dc_tilde_raw = self.tanh_derivative(c_tilde[t]) * dc_tilde
            
            di = dc * c_tilde[t]
            di_raw = self.sigmoid_derivative(i[t]) * di
            
            df = dc * (c[-1] if t == 0 else c[t - 1])
            df_raw = self.sigmoid_derivative(f[t]) * df
            
            # Accumulate weight gradients
            dWf += np.dot(df_raw, concat[t].T)
            dWi += np.dot(di_raw, concat[t].T)
            dWc += np.dot(dc_tilde_raw, concat[t].T)
            dWo += np.dot(do_raw, concat[t].T)
            
            dbf += df_raw
            dbi += di_raw
            dbc += dc_tilde_raw
            dbo += do_raw
            
            # Backprop into the concat [hidden_prev, x_t]
            dconcat = (
                np.dot(self.Wf.T, df_raw) +
                np.dot(self.Wi.T, di_raw) +
                np.dot(self.Wc.T, dc_tilde_raw) +
                np.dot(self.Wo.T, do_raw)
            )
            
            # Hidden state carries over to previous step
            dh_next = dconcat[:self.hidden_dim, :]
            dc_next = dc * f[t]
            
        # Clip gradients to prevent exploding gradients
        for grad in [dWf, dWi, dWc, dWo, dWy, dbf, dbi, dbc, dbo, dby]:
            np.clip(grad, -1.0, 1.0, out=grad)
            
        return dWf, dWi, dWc, dWo, dWy, dbf, dbi, dbc, dbo, dby

    def update_params(self, grads):
        dWf, dWi, dWc, dWo, dWy, dbf, dbi, dbc, dbo, dby = grads
        
        self.Wf -= self.lr * dWf
        self.Wi -= self.lr * dWi
        self.Wc -= self.lr * dWc
        self.Wo -= self.lr * dWo
        self.Wy -= self.lr * dWy
        
        self.bf -= self.lr * dbf
        self.bi -= self.lr * dbi
        self.bc -= self.lr * dbc
        self.bo -= self.lr * dbo
        
        self.by -= self.lr * dby

    def fit(self, X, y, epochs=10):
        # X: array of shape (num_samples, seq_len, input_dim)
        # y: array of shape (num_samples, output_dim)
        num_samples = X.shape[0]
        
        for epoch in range(epochs):
            total_loss = 0
            for i in range(num_samples):
                X_seq = X[i]
                y_target = y[i].reshape(-1, 1)
                
                # Forward pass
                y_pred, cache = self.forward(X_seq)
                
                # Compute Loss (MSE)
                loss = 0.5 * np.sum((y_pred - y_target) ** 2)
                total_loss += loss
                
                # Backward pass
                d_out = y_pred - y_target
                grads = self.backward(d_out, cache)
                
                # Update weights
                self.update_params(grads)
                
            # Log epoch progress
            mean_loss = total_loss / num_samples
            if epoch % 5 == 0 or epoch == epochs - 1:
                print(f"Epoch {epoch}/{epochs} - Loss: {mean_loss:.6f}")


# ==========================================
# 2. Linear Regression Feature Generator
# ==========================================
def create_regression_features(data, window_size=5):
    # data: 1D numpy array of historical prices
    # Returns X (features) and y (targets) for rolling window
    X, y = [], []
    for i in range(len(data) - window_size):
        X.append(data[i : i + window_size])
        y.append(data[i + window_size])
    return np.array(X), np.array(y)
