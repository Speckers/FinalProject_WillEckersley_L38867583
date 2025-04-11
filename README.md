# FinalProject_WillEckersley_L38867583
AI-Powered Football Squad Planner
Project Overview
The AI-Powered Football Squad Planner is a web-based tool that uses machine learning to generate optimal starting XI formations for a football team based on player performance data. This project utilises Python for data manipulation and machine learning, PHP for web application integration, and MySQL for data storage. The goal of the project is to create a system that selects players based on performance metrics such as goals, assists, pass completion percentage, and other key stats.

Key Features
Dynamic Squad Selection: Generate a starting eleven based on player statistics and a user-defined formation (e.g., 4-3-3).

Customisable Metrics: Users can select the performance metric used to determine the best squad (e.g., goals, assists, pass completion percentage).

Web Interface: A web-based interface built using PHP allows users to interact with the tool, input criteria, and view all players and stats in the database.

Data Integration: Connects to a MySQL database that stores historical player data, which is used to train machine learning models and make predictions.

Model Evaluation: Implements machine learning models such as Gradient Boosting to predict optimal squad members based on past performance, with evaluation metrics such as MAE, MSE, and R².

Technologies Used
Python: For data processing, machine learning, and model evaluation (using pandas, scikit-learn).

PHP: For server-side logic and integration with the frontend.

MySQL: To store football player data, including performance metrics and statistics.

HTML/CSS/JavaScript: For frontend development, allowing users to interact with the web interface

Known Issues
PHP-Python Integration: The communication between PHP and Python, which allows the web app to execute the machine learning model, is still a work in progress. The method used (exec()) is prone to limitations, and further optimisation is needed for smooth integration.

Real-Time Data Integration: The current system does not yet incorporate real-time player data, which would significantly enhance the accuracy of predictions.

Future Enhancements
Real-Time Data Integration: Implement APIs that fetch live player stats to allow the squad to adjust based on current form.

Model Optimisation: Experiment with other machine learning models like Random Forests and Neural Networks for improved accuracy.

Scalability Improvements: Optimise the backend to handle larger datasets and ensure better performance under heavy load.

UI Improvements: Add interactive features like drag-and-drop squad selection, improved visualisations, and better customisation options for users.

PHP-Python Integration: Fix the integration issue

Installation:
1: Clone repository: 
