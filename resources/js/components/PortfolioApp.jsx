import React, { Component } from 'react';
import ReactDOM from 'react-dom/client'; 

import Portfolio from './Portfolio';

function PortfolioApp() {
    const root = ReactDOM.createRoot(document.getElementById('portfolio')); 
    root.render(<Portfolio />);  
}


export default PortfolioApp;