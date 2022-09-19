### **Loan Approval Api App**

**Version** 7.0

This app is a API app that will provide APIS for creation and approval of loans.

### **Project setup**

Execute below command in terminal inside project folder

composer install

Rename .env.example to .env and Update .env file with appropriate database credentials for below parameters

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=loan_approval_demo
DB_USERNAME=root
DB_PASSWORD=

php artisan config:cache

### **Database setup**

Please open project folder in terminal and execute below commands

php artisan migrate

php artisan db:seed --class=UserSeeder

To initalize server on local execute below command

php artisan serve --port=8000

### **List of APIs**

1. http://127.0.0.1:8000/api/applyloan 
2. http://127.0.0.1:8000/api/approveloan 
3. http://127.0.0.1:8000/api/payemi 

### **API Flow**

1. You may apply for a loan by visiting `/api/applyloan` Fields required for this POST request are `username`, `secret`, `amount` & `loan_tenure`

    Expected Response: `{"status":true,"status_message":"Loan applicaton successful!"}`

2. To approve your loan you may head to `/api/approveloan` Fields required for this POST request are `username`, `secret` & `loan_id`

    Expected Response: `{"status":true,"status_message":"Loan applicaton approved!"}`

3. To pay EMIs you may visit `/api/payemi` Fields required for this POST request are `username`, `secret`, `schedule_id` & `amount`

    Expected Response: `{"status":true,"status_message":"EMI Paid successfully!"}`