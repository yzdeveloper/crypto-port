import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client'; 

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes React and other helpers. It's a great starting point while
 * building robust, powerful web applications using React + Laravel.
 */

import './bootstrap';

/**
 * Next, we will create a fresh React component instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import ActionButtons from './components/ActionButtons';
import PortfolioPerformance from './components/PortfolioPerformance';
import PortfolioTable from './components/PortfolioTable';

function App() {
  const [portfolio, setPortfolio] = useState([]);
  const [cash, setCash] = useState(0);
  const [invested, setInvested] = useState(0);
  const [releasedPnL, setReleasedPnL] = useState(0);
  const [unreleasedPnL, setUnreleasedPnL] = useState(0);

  function getInvested(portfolio) {
    let sum = 0.
    for (inst of portfolio) {
        sum += inst.buyPrice * inst.quantiry;
    }

    return sum;
  }


  useEffect(() => {
    fetch('/api/cash')
      .then(cashStr => {
        console.log(`cash: ${cashStr}`);
        setCash(parseFloat(cashStr));
      })
      .catch(error => console.error('Error fetching data:', error));
  }, []);


  // Fetch from the backend
  useEffect(() => {
    fetch('/api/load_portfolio')
      .then(response => response.json())
      .then(port => {
        setPortfolio(port.porfolio);
        setInvested(port.cash + getInvested(port.porfolio));
        setReleasedPnL(port.releasedPnL);
        setUnreleasedPnL(0);
      })
      .catch(error => console.error('Error fetching data:', error));
  }, []);

  return (
    <div className="App">
      <ActionButtons />
      <PortfolioPerformance 
        invested={invested} 
        releasedPnL={releasedPnL} 
        unreleasedPnL={0} 
      />
      <PortfolioTable data={portfolio} />
    </div>
  );
}

export default App;


// Create a root container and render the App component into the DOM element with id "root"
const root = ReactDOM.createRoot(document.getElementById('App')); // Get the element with id "root"
root.render(<App />);  // Attach the App component to the root element
