import React from 'react';

const PortfolioPerformance = ({ invested, releasedPnL, unreleasedPnL }) => {
  return (
    <div className="portfolio-performance">
      <div className="details-box">
        <h3>Invested</h3>
        <input type="text" value={invested} readOnly />
      </div>
      <div className="details-box">
        <h3>Released PnL</h3>
        <input type="text" value={releasedPnL} readOnly />
      </div>
      <div className="details-box">
        <h3>Unreleased PnL</h3>
        <input type="text" value={unreleasedPnL} readOnly />
      </div>
    </div>
  );
};

export default PortfolioPerformance;