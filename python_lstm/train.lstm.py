import pandas as pd
import numpy as np
from sklearn.preprocessing import MinMaxScaler, LabelEncoder
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report, confusion_matrix
import tensorflow as tf
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import LSTM, Dense, Dropout
from tensorflow.keras.callbacks import EarlyStopping
import matplotlib.pyplot as plt
import seaborn as sns
import joblib
import warnings
warnings.filterwarnings('ignore')

print("=" * 60)
print("LSTM TRAINING UNTUK DETEKSI ANOMALI INFUS")
print("=" * 60)

print("\n📂 Loading dataset...")
df = pd.read_csv('../app/lstm_dataset_ESP32-F029.csv')
print(f"✅ Data loaded: {len(df)} rows")

print("\n📊 Label distribution:")
print(df['label'].value_counts())

print("\n📊 Status distribution:")
print(df['status'].value_counts())

print("\n🔧 Preprocessing...")

# Encode label: normal=0, anomaly=1
le = LabelEncoder()
df['label_encoded'] = le.fit_transform(df['label'])
print(f"Label mapping: {dict(zip(le.classes_, le.transform(le.classes_)))}")

# Pilih features untuk training
features = ['total_drops', 'current_tpm', 'interval_drops']
target = 'label_encoded'

# Normalisasi features
scaler = MinMaxScaler()
df_scaled = df.copy()
df_scaled[features] = scaler.fit_transform(df[features])

print("✅ Scaled features:")
print(df_scaled[features].describe())

print("\n📐 Creating sequences...")

def create_sequences(X, y, time_steps=5):
    """Membuat sequences untuk input LSTM"""
    Xs, ys = [], []
    for i in range(len(X) - time_steps):
        Xs.append(X[i:i+time_steps])
        ys.append(y[i+time_steps])
    return np.array(Xs), np.array(ys)

TIME_STEPS = 5  # Window 5 menit

X = df_scaled[features].values
y = df_scaled[target].values

X_seq, y_seq = create_sequences(X, y, TIME_STEPS)

print(f"✅ X shape: {X_seq.shape}")  
print(f"✅ y shape: {y_seq.shape}")

print("\n🔀 Splitting data (80:20)...")

X_train, X_test, y_train, y_test = train_test_split(
    X_seq, y_seq, test_size=0.2, random_state=42, stratify=y_seq
)

print(f"X_train: {X_train.shape}, y_train: {y_train.shape}")
print(f"X_test:  {X_test.shape},  y_test:  {y_test.shape}")

print("\n🏗️  Building LSTM model...")

model = Sequential([
    # LSTM Layer 1
    LSTM(64, return_sequences=True, input_shape=(TIME_STEPS, len(features))),
    Dropout(0.3),
    
    # LSTM Layer 2
    LSTM(32, return_sequences=False),
    Dropout(0.3),
    
    # Dense Layers
    Dense(16, activation='relu'),
    Dropout(0.2),
    Dense(1, activation='sigmoid')  # Binary classification
])

model.compile(
    optimizer='adam',
    loss='binary_crossentropy',
    metrics=['accuracy']
)

model.summary()

print("\n🚀 Training model...")

early_stop = EarlyStopping(
    monitor='val_loss',
    patience=10,
    restore_best_weights=True,
    verbose=1
)

history = model.fit(
    X_train, y_train,
    epochs=100,
    batch_size=32,
    validation_split=0.2,
    callbacks=[early_stop],
    verbose=1
)

print("✅ Training selesai!")

print("\n📊 Evaluating model...")

# Predict
y_pred_prob = model.predict(X_test)
y_pred = (y_pred_prob > 0.5).astype(int)

# Classification report
print("\n📋 Classification Report:")
print(classification_report(y_test, y_pred, target_names=le.classes_))

# Confusion matrix
cm = confusion_matrix(y_test, y_pred)
print("\n📊 Confusion Matrix:")
print(cm)

# Hitung akurasi per kelas
tn, fp, fn, tp = cm.ravel() if cm.size == 4 else (0, 0, 0, 0)
print(f"\nTrue Positive (Anomali terdeteksi): {tp}")
print(f"True Negative (Normal terdeteksi): {tn}")
print(f"False Positive (False alarm): {fp}")
print(f"False Negative (Anomali terlewat): {fn}")

print("\n📈 Generating plots...")

fig, axes = plt.subplots(1, 3, figsize=(15, 5))

# Plot 1: Training history
axes[0].plot(history.history['accuracy'], label='Train Accuracy')
axes[0].plot(history.history['val_accuracy'], label='Val Accuracy')
axes[0].set_title('Model Accuracy')
axes[0].set_xlabel('Epoch')
axes[0].set_ylabel('Accuracy')
axes[0].legend()

# Plot 2: Training loss
axes[1].plot(history.history['loss'], label='Train Loss')
axes[1].plot(history.history['val_loss'], label='Val Loss')
axes[1].set_title('Model Loss')
axes[1].set_xlabel('Epoch')
axes[1].set_ylabel('Loss')
axes[1].legend()

# Plot 3: Confusion Matrix
sns.heatmap(cm, annot=True, fmt='d', cmap='Blues', ax=axes[2],
            xticklabels=le.classes_, yticklabels=le.classes_)
axes[2].set_title('Confusion Matrix')
axes[2].set_xlabel('Predicted')
axes[2].set_ylabel('Actual')

plt.tight_layout()
plt.savefig('training_results.png', dpi=150)
print("✅ Plots saved to: training_results.png")

print("\n💾 Saving model...")

# Save Keras model
model.save('model/lstm_infusion_model.h5')
print("✅ Model saved: model/lstm_infusion_model.h5")

# Save scaler
joblib.dump(scaler, 'model/scaler.pkl')
print("✅ Scaler saved: model/scaler.pkl")

# Save label encoder
joblib.dump(le, 'model/label_encoder.pkl')
print("✅ Label encoder saved: model/label_encoder.pkl")

# Save config
config = {
    'time_steps': TIME_STEPS,
    'features': features,
    'model_path': 'model/lstm_infusion_model.h5',
    'scaler_path': 'model/scaler.pkl',
    'encoder_path': 'model/label_encoder.pkl'
}
joblib.dump(config, 'model/config.pkl')
print("✅ Config saved: model/config.pkl")

final_accuracy = history.history['val_accuracy'][-1] * 100
print("\n" + "=" * 60)
print("🎉 TRAINING COMPLETED!")
print(f"📊 Final Validation Accuracy: {final_accuracy:.2f}%")
print(f"📁 Model saved to: model/")
print("=" * 60)