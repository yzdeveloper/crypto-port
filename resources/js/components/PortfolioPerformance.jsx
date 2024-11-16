import React from 'react';

const PortfolioPerformance = ({ invested, releasedPnL, unreleasedPnL }) => {
  return (
    <div className="portfolio-performance">
      <div className="details-box">
        <h4>Invested</h4>
        <input type="text" value={invested} readOnly />
      </div>
      <div className="details-box">
        <h4>Released PnL</h4>
        <input type="text" value={releasedPnL} readOnly />
      </div>
      <div className="details-box">
        <h4>Unreleased PnL</h4>
        <input type="text" value={unreleasedPnL} readOnly />
      </div>
    </div>
  );
};

export default PortfolioPerformance;