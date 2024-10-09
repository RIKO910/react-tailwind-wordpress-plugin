// src/components/ Dashboard.jsx

import React, { useState, useEffect } from 'react';

const Dashboard = () => {
    const [isEnabled, setIsEnabled] = useState(null);  // Tracks whether "yes" or "no" is selected
    const [saveStatus, setSaveStatus] = useState(null);  // Tracks save operation status
    const [isLoading, setIsLoading] = useState(true);  // Tracks if data is being fetched

    // Fetch initial data from the WordPress options table on component load
    useEffect(() => {
        fetch('/wp-json/jobplace/v1/get_option')
            .then(response => response.json())
            .then(data => {
                // Convert the stored value from the database to boolean ("yes" -> true, "no" -> false)
                setIsEnabled(data.isEnabled === "yes");  // Treat "yes" as enabled and "no" as disabled
                setIsLoading(false);  // Set loading to false once data is fetched
            })
            .catch(error => {
                console.error('Error:', error);
                setIsLoading(false);  // Set loading to false even if there is an error
            });
    }, []);

    const handleChange = (event) => {
        const value = event.target.value === 'true';
        setIsEnabled(value);  // Set the new value when user selects a radio button
        saveOption(value);    // Save the option immediately after the user changes the selection
    };

    const saveOption = (isEnabled) => {
        // Send data to update wp_options table via REST API
        fetch('/wp-json/jobplace/v1/update_option', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ isEnabled: isEnabled ? "yes" : "no" }),  // Store "yes" for enabled and "no" for disabled
        })
            .then(response => response.json())
            .then(data => {
                console.log('Option saved:', data);
                setSaveStatus('success');  // Update save status to success
            })
            .catch(error => {
                console.error('Error:', error);
                setSaveStatus('error');  // Update save status to error
            });
    };

    if (isLoading) {
        return <div>Loading...</div>;  // Show loading message until data is fetched
    }

    return (
        <div className='dashboard'>
            <div className="card ">
                <h3>Enable/Disable Feature</h3>
                <div>
                    {/* Correctly handle the radio buttons based on the isEnabled state */}
                    <label>
                        <input
                            type="radio"
                            value={true}
                            checked={isEnabled === true}  // Radio button for "Enable"
                            onChange={handleChange}
                        />
                        Enable
                    </label>
                    <label>
                        <input
                            type="radio"
                            value={false}
                            checked={isEnabled === false}  // Radio button for "Disable"
                            onChange={handleChange}
                        />
                        Disable
                    </label>
                </div>

                {/* Show feedback based on save status */}
                {saveStatus === 'success' && <p style={{ color: 'green' }}>Option saved successfully!</p>}
                {saveStatus === 'error' && <p style={{ color: 'red' }}>Failed to save the option. Please try again.</p>}
            </div>
        </div>
    );
};

export default Dashboard;
