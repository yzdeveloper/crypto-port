
import React from 'react';

const PortfolioTable = ({ data }) => {
  return (
    <table className="portfolio-table">
      <thead>
        <tr>
          <th>Instrument</th>
          <th>Quantity</th>
          <th>Bought Price</th>
          <th>Bought Value</th>
          <th>Current Price</th>
          <th>Current Value</th>
          <th>PnL</th>
        </tr>
      </thead>
      <tbody>
        {data.map((row, index) => (
          <tr key={index}>
            <td>{row.instrument}</td>
            <td>{row.quantity}</td>
            <td>{row.boughtPrice}</td>
            <td>{row.value}</td>
            <td>{row.price}</td>
            <td>{row.value}</td>
            <td>{row.pnl}</td>
          </tr>
        ))}
      </tbody>
    </table>
  );
};

export default PortfolioTable;