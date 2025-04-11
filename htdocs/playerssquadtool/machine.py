import sys
import mysql.connector
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
import json

from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_squared_error, mean_absolute_error, r2_score
from sklearn.linear_model import LinearRegression
from sklearn.ensemble import RandomForestRegressor, GradientBoostingRegressor
from sklearn.svm import SVR

#DB Connection
db_connection = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="players"
)

#Get CLI Arguments
if len(sys.argv) < 3:
    print("Error: Not enough arguments. Please provide both formation and target metric.")
    sys.exit(1)

formation_input = sys.argv[1]
target_metric = sys.argv[2]

print(f"Formation Input: {formation_input}")
print(f"Target Metric: {target_metric}")

#Parse Formation 
def parse_formation(formation_str):
    parts = list(map(int, formation_str.split('-')))
    return {
        'GK': 1,
        'DEF': parts[0],
        'MID': parts[1],
        'FWD': parts[2]
    }

formation_counts = parse_formation(formation_input)

#SQL Query 
query = f"""
    SELECT 
        Player, Nation, Comp, Squad, Pos,
        Goals, Assists, Shots, `SoT`, `SoT%`, `G/Sh`, `ShoFK`, `ShoPK`, `PKatt`,
        `PasTotAtt`, `PasTotCmp%`, `PasShoAtt`, `PasShoCmp%`, `PasMedAtt`, `PasMedCmp%`, 
        `PasLonAtt`, `PasLonCmp%`, PPA, `CrsPA`, `PasProg`, `PasAtt`, `PasCrs`, 
        TI, CK, `PasCmp`, SCA, GCA, Tkl, TklWon, `TklDri%`, `TklDriPast`, 
        BlkSh, `Int`, `Clr`, Touches, ToAtt, ToSuc, `ToSuc%`, ToTkl, 
        Carries, CarProg, Car3rd, Rec, CrdY, CrdR, Fls, Fld, Off, 
        Crs, TklW, `PKwon`, `PKcon`, Recov, `AerWon`, `AerLost`, `AerWon%`
    FROM 2022_2023stats2_in_
    WHERE `{target_metric}` IS NOT NULL
"""

#Load Data 
try:
    player_data = pd.read_sql(query, db_connection)
except Exception as e:
    print(f"Error fetching data: {e}")
    sys.exit(1)

#Fix Percent Columns 
percent_columns = ['SoT%', 'G/Sh', 'PasTotCmp%', 'PasShoCmp%', 'PasMedCmp%', 'PasLonCmp%', 'ToSuc%', 'TklDri%', 'AerWon%']
for col in percent_columns:
    if col in player_data.columns:
        player_data[col] = player_data[col] / 100

#Feature and Target Setup 
features = player_data.select_dtypes(include='number').drop(columns=[target_metric]).columns.tolist()
X = player_data[features]
y = player_data[target_metric]

#Train and Test Split 
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

#Model Comparison 
models = {
    "Linear Regression": LinearRegression(),
    "Random Forest": RandomForestRegressor(random_state=42),
    "Gradient Boosting": GradientBoostingRegressor(random_state=42),
    "Support Vector Regressor": SVR()
}

print("\n--- Model Comparison ---")
for name, model_instance in models.items():
    model_instance.fit(X_train, y_train)
    y_pred_model = model_instance.predict(X_test)
    mae = mean_absolute_error(y_test, y_pred_model)
    mse = mean_squared_error(y_test, y_pred_model)
    r2 = r2_score(y_test, y_pred_model)
    print(f"\n{name}:")
    print(f"  MAE: {mae:.4f}")
    print(f"  MSE: {mse:.4f}")
    print(f"  R^2: {r2:.4f}")


model = GradientBoostingRegressor()
model.fit(X_train, y_train)

#Evaluation 
y_pred = model.predict(X_test)
print("\n--- Model Evaluation (Gradient Boosting) ---")
print(f"Mean Absolute Error: {mean_absolute_error(y_test, y_pred):.4f}")
print(f"Mean Squared Error: {mean_squared_error(y_test, y_pred):.4f}")
print(f"R^2 Score: {r2_score(y_test, y_pred):.4f}")

#Feature Importance 
importances = pd.Series(model.feature_importances_, index=features).sort_values(ascending=False)

#Plot Top 10 Features 
plt.figure(figsize=(10, 5))
sns.barplot(x=importances.head(10), y=importances.head(10).index, color="blue")
plt.title(f"Top 10 Feature Importances for Predicting {target_metric}")
plt.xlabel("Importance Score")
plt.ylabel("Feature")
plt.tight_layout()
plt.savefig(f"feature_importance_{target_metric}.png")

#Add Prediction Columns 
player_data['Predicted Metric'] = model.predict(X)
player_data['Actual Metric'] = y
player_data['Hybrid Score'] = (player_data['Predicted Metric'] + player_data['Actual Metric']) / 2

#Select Starting XI Based on Formation 
starting_eleven = []
for pos, count in formation_counts.items():
    players = player_data[player_data['Pos'] == pos].sort_values(by='Hybrid Score', ascending=False).head(count)
    starting_eleven.append(players)

#Final Squad Output 
final_squad = pd.concat(starting_eleven)

#SON Output 
final_squad_dict = {
    "formation": formation_input,
    "starting_eleven": final_squad[['Player', 'Pos', 'Actual Metric', 'Predicted Metric', 'Hybrid Score']].to_dict(orient='records')
}

print(json.dumps(final_squad_dict, indent=4))



