import React from 'react';
import Dashboard from './components/Dashboard';

const App = () => {
    return (
        <div className="w-full">
            <div className="bg-purple-600 text-white p-4 text-center">
                <h1 className="text-2xl font-bold">Toggle Payment By Shipping 1.0.0</h1>
                <p className="text-sm mt-2">
                    Thank you for using our plugin! If you are satisfied, please reward it a full five-star ★★★★★
                    rating.
                </p>
            </div>
            <Dashboard/>
        </div>
    );
};

export default App;
