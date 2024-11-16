import React from 'react';

const ActionButtons = () => {
  const handleButtonClick = (action) => {
    console.log(`${action} button clicked`);
    // Here you could trigger API calls to the backend based on the action
  };

  return (
    <div className="header-buttons">
      <button onClick={() => handleButtonClick('Add')}>Add</button>
      <button onClick={() => handleButtonClick('Withdraw')}>Withdraw</button>
      <button onClick={() => handleButtonClick('Buy')}>Buy</button>
      <button onClick={() => handleButtonClick('Sell')}>Sell</button>
    </div>
  );
};

export default ActionButtons;