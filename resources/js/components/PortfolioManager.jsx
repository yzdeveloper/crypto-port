import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';

import HeaderButtons from './HeaderButtons';
import InvestmentDetails from './InvestmentDetails';
import PortfolioTable from './PortfolioTable';

function PortfolioManager() {
  const [portfolioData, setPortfolioData] = useState([]);
  const [invested, setInvested] = useState(0.);
  const [releasedPnL, setReleasedPnL] = useState(0.);
  const [unreleasedPnL, setUnreleasedPnL] = useState(0.);
  const [cash, setCash] = useState(0.);

  // Fetch financial data from the backend
  useEffect(() => {
    fetch('/api/get-portfolio')
      .then(response => response.json())
      .then(data => {
        setFinancialData(data);
        setInvested(data.invested);
        setReleasedPnL(data.releasedPnL);
        setUnreleasedPnL(data.unreleasedPnL);
      })
      .catch(error => console.error('Error fetching data:', error));
  }, []);

  return (
    <div className="PortfolioManager">
      <HeaderButtons />
      <InvestmentDetails
        invested={invested}
        releasedPnL={releasedPnL}
        unreleasedPnL={0.}
      />
      <PortfolioTable data={portfolioData} />
    </div>
  );
}

export default PortfolioManager;

const rootElement = document.getElementById('portfolioManager')
const root = createRoot(rootElement);

root.render(<PortfolioManager />);