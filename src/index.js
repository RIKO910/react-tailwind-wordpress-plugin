import React from 'react';
import { render } from '@wordpress/element';
import App from './App';
import './style/main.css'; // Import Tailwind's compiled CSS

// Render the App component into the DOM
document.addEventListener('DOMContentLoaded', function() {
    const app = document.getElementById('react-setup-app');
    if (app) {
        render(<App />, app);
    }
});
