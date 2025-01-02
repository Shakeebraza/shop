<style>
    .transaction_btn {
        box-shadow: 2px 3px black;
        background-color: #04AA6D;
        border: none;
        color: white;
        padding: 6px 32px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 12px;
    }
    .cancle_transaction_btn {
        box-shadow: 2px 3px black;
        background-color: red;
        border: none;
        color: white;
        padding: 6px 32px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 12px;
    }

    .pay_now_btn {
        box-shadow: 2px 3px black;
        background-color:orange;
        border: none;
        color: black;
        padding: 6px 32px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 12px;
    }

    .expired-status {
        text-align: center;
        font-weight: bold;
        color: red;
    }


body.popup-active {
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px); 
    background: rgba(255, 255, 255, 0.5); 
    transition: backdrop-filter 0.3s, background 0.3s;
}
.dataTables_filter {
    background-color: #0c182f;
    color: white;
    padding: 8px 12px;
    border-radius: 5px;
    display: inline-block;
    font-weight: bold;
    margin: 10px 0;
}

.dataTables_filter label {
    color: white;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.dataTables_filter input[type="search"] {
    padding: 6px 10px;
    border: 1px solid #0c182f;
    border-radius: 3px;
    font-size: 14px;
    color: #0c182f;
    outline: none;
    width: 200px;
    transition: box-shadow 0.2s ease;
}

.dataTables_filter input[type="search"]:hover,
.dataTables_filter input[type="search"]:focus {
    box-shadow: 0 0 5px #0c182f;
}

.dataTables_paginate {
    padding: 10px;
    background-color: #0c182f;
    color: white;
    border-radius: 5px;
    font-weight: bold;
    display: inline-block;
}

.dataTables_paginate .paginate_button {
    background-color: #0c182f;
    color: white;
    padding: 5px 10px;
    border: 1px solid #0c182f;
    border-radius: 3px;
    margin: 0 5px;
    cursor: pointer;
}

.dataTables_paginate .paginate_button:hover {
    background-color: #2a437b;
}

.dataTables_paginate .paginate_button.current {
    background-color: #2a437b;
    border-color: #2a437b;
}

.dataTables_paginate .paginate_button.disabled {
    /* background-color: #e0e0e0; */
    color: #bdbdbd;
    cursor: not-allowed;
}

.dataTables_length {
    background-color: #0c182f;
    color: white;
    padding: 8px 12px;
    border-radius: 5px;
    font-weight: bold;
    display: inline-block;
}

.dataTables_length label {
    color: white;
    font-size: 14px;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 5px;
}

.dataTables_length select {
    background-color: white;
    color: #0c182f;
    border: 1px solid #0c182f;
    padding: 4px 6px;
    border-radius: 3px;
    font-size: 14px;
    cursor: pointer;
}

.dataTables_length select:hover {
    background-color: #f0f0f0;
}
#add-money-form {
    width: 100%;
    max-width: 500px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    font-family: Arial, sans-serif;
}

#add-money-form h2 {
    font-size: 24px;
    color: #333;
    text-align: center;
    margin-bottom: 20px;
}

/* Label Styling */
#add-money-form label {
    font-size: 16px;
    color: #555;
    margin-bottom: 8px;
    display: block;
}

/* Select and Input Field Styling */
#add-money-form select, #add-money-form input {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 16px;
    color: #333;
    transition: all 0.3s ease;
}

#add-money-form select:focus, #add-money-form input:focus {
    border-color: #0c182f;
    outline: none;
    box-shadow: 0 0 5px rgba(59, 89, 152, 0.3);
}

/* Hidden Payment Info Styling */
#payment-info {
    display: none;
    background-color: #f1f1f1;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

#payment-info p {
    font-size: 16px;
    color: #444;
}

/* Submit Button Styling */
#add-money-form input[type="submit"] {
    background-color: #0c182f;
    color: white;
    border: none;
    padding: 12px;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    width: 100%;
}

#add-money-form input[type="submit"]:hover {
    background-color: #2a437b;
}

/* Mobile Responsive Design */
@media (max-width: 600px) {
    #add-money-form {
        padding: 15px;
    }

    #add-money-form h2 {
        font-size: 20px;
    }

    #add-money-form label {
        font-size: 14px;
    }

    #add-money-form select, #add-money-form input {
        font-size: 14px;
    }

    #add-money-form input[type="submit"] {
        font-size: 14px;
    }
}
.expired-status {
    color: red;
    font-weight: bold;
}

.confirmed-status {
    color: green;
    font-weight: bold;
}

.pending-status {
    color: orange;
    font-weight: bold;
}

.insufficient-status {
    color: maroon;
    font-weight: bold;
}
.insufficient-status:hover{
    cursor: pointer;
}
.receiving-status {
    color: #0c182f;
    font-weight: bold;
}

.default-status {
    color: #333;
    font-weight: normal;
}

#errorPopup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
}

#errorPopupContent {
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    text-align: center;
}

#errorPopup #errorMessage {
    margin-bottom: 20px;
}

#errorPopup button {
    padding: 10px 20px;
    background-color: red;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
#closeModalBtn {
        background-color: #6c5ce7; 
        color: #fff; 
        border: none;
        font-size: 20px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.3s ease; 
    }

   
    #closeModalBtn:hover {
        background-color: red; 
        transform: rotate(360deg);
    }

    @keyframes dot-animation {
    0% { content: ""; }
    25% { content: "."; }
    50% { content: ".."; }
    75% { content: "..."; }
    100% { content: ""; }
}

.dots::after {
    content: "";
    animation: dot-animation 0.5s infinite steps(4, start);
}
.button-container {
    display: flex;
    gap: 10px; 
    align-items: center; 
    justify-content: flex-start;
}


@keyframes shake-up-down {
    0% { transform: translateY(0); }
    25% { transform: translateY(-5px); }
    50% { transform: translateY(5px); }
    75% { transform: translateY(-5px); }
    100% { transform: translateY(0); }
}


.shake {
    animation: shake-up-down 0.5s ease-in-out;
}

#rules-btn:hover {
    animation: shake-up-down 0.5s ease-in-out;
}

#transaction-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-family: Arial, sans-serif;
}

#transaction-table thead {
    background-color: #4CAF50;
    color: white;
}

#transaction-table th, #transaction-table td {
    padding: 12px 15px;
    text-align: left;
    border: 1px solid #ddd;
}

#transaction-table tr:nth-child(even) {
    background-color: #f2f2f2;
}

#transaction-table tr:hover {
    background-color: #ddd;
}

#transaction-table th {
    font-size: 16px;
    font-weight: bold;
}

#transaction-table td {
    font-size: 14px;
}

#transaction-table td a {
    text-decoration: none;
    color: #007BFF;
}

#transaction-table td a:hover {
    text-decoration: underline;
}

</style>