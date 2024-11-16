import React, { useState, useEffect } from 'react';

import ActionButtons from './ActionButtons';
import PortfolioPerformance from './PortfolioPerformance';
import PortfolioTable from './PortfolioTable';


function Portfolio() {
  const [portfolio, setPortfolio] = useState([]);
  const [cash, setCash] = useState(0.);
  const [cashAdd, setCashAdd] = useState(0.);

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
        .then(response => {
            if (!response.ok) {
                throw new Error(response.statusText);
            }

            console.log(JSON.stringify(response));
            return response.text();
        })
        .then(cashStr => {        
            console.log(typeof cashStr)
            console.log(`cash: ${cashStr} `);
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
    <div className='Portfolio'>
        <div class='cashBox'>
            <div class='horizontal'>
                <div class='horizontal'>Cash</div>
                <input type="text" value={cash} readOnly />
                <input type="text" value={cashAdd} />
                <div class='vertical'>
                    <button>Add</button>
                    <button>Withdraw</button>
                </div>
            </div>
        </div>  
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


export default Portfolio;

