import pandas as pd                                                                # type: ignore
import scipy                                                                       # type: ignore
import numpy as np                                                                 # type: ignore
from sklearn.preprocessing import MinMaxScaler                                     # type: ignore
import seaborn as sns                                                              # type: ignore
import matplotlib.pyplot as plt                                                    # type: ignore
from sklearn.model_selection import train_test_split                               # type: ignore
import plotly.express as px                                                        # type: ignore
from sklearn.linear_model import LinearRegression                                  # type: ignore
from sklearn.experimental import enable_hist_gradient_boosting                     # type: ignore
from sklearn.ensemble import HistGradientBoostingRegressor                         # type: ignore
import os
import subprocess
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score       # type: ignore


os.environ['LOKY_MAX_CPU_COUNT'] = '4'  
cpu_info = subprocess.run(
    "wmic CPU Get NumberOfCores /Format:csv".split(),
    capture_output=True,
    text=True,
    shell=True
)
print(cpu_info.stdout)
#^^ When first trying to implement the HistGradientBoostingRegressor a WinError2 but this code fixed it.
#^^^ The code sets an environmentale variable to limit the number of CPU cores used,
#^^^ It then runs a command to get the number of physical CPU cores using WMIC,
#^^^ then captures and prints the output of the command

players = pd.read_csv('C:\\xampp\\htdocs\\playerssquadtool\\ml\\2022-2023stats2(in).csv', encoding='latin1')

players.columns.rename("PlNum", inplace=True)
#^^ changes index column name
print(players.isna().values.any())
#^^ Checks for null values
players.duplicated().sum()
#^^ Checks for duplicates
print(players.sample(5))
#^^ shows a random sample of 5 players in the dataset
print(players.info(show_counts=True,verbose=True))
#^^ show all columns
players_num =players.drop(columns=["Player","Nation","Pos","Squad","Comp"]) 
print(players_num)
#^^ Creates dataframe only for numeric columns
players_num = players_num.apply(pd.to_numeric)
print(players_num.dtypes)
#^^Changes dtype of numeric columns
players1 = pd.concat((players[["Player","Nation","Pos","Squad","Comp"]], players_num), axis=1)
#^^ combines players and players_num horizontally with player info first then play stats
print(players1.info(verbose=True, show_counts=True))
#^^ displays info about the new dataframe
print(players1.sample(10))
#^^ outputs the new players1 dataframe with 10 example players
players1 = players1.apply(pd.to_numeric, errors='coerce')
#^^ Convert all columns to numeric, coercing errors to NaN
print("NaN values in each column:")
print(players1.isna().sum())
#^^ Check for NaN values
players1.fillna(players1.mean(), inplace=True)
#^^ Fill NaN values with column mean
#
#VV Check if the dataset is empty after filling NaN values
if players1.empty:
    print("Dataset is empty after filling NaN values.")
else:
    
    features = players1.drop(columns=["Player"])
    target = players1["PasTotCmp%"]  
    #^^ Define features and target, with an example target
    
    X_train, X_test, y_train, y_test = train_test_split(features, target, test_size=0.2, random_state=42)
    #^^ Split the data
    #VV Check if the train set is empty
    if X_train.empty or y_train.empty:
        print("Train set is empty.")
    else:
        
        model = HistGradientBoostingRegressor()
        model.fit(X_train, y_train)
        #^^ Train the model
        
        print("Model training completed.")
        print(f"X_test shape: {X_test.shape}")
        print(f"y_test shape: {y_test.shape}")
        #^^ Debug 
        
        r2_score = model.score(X_test, y_test)
        print(f"Model R^2 score: {r2_score}")
        #^^ model evaluate score


