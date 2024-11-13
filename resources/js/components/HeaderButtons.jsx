import React from 'react';
import BuyButton from './BuyButton';

const HeaderButtons = () => {
  const handleButtonClick = (action) => {
    console.log(`${action} button clicked`);
    // Here you could trigger API calls to the backend based on the action
  };

  return (
    <div className="header-buttons">
      <button onClick={() => handleButtonClick('Add')}>Add</button>
      <button onClick={() => handleButtonClick('Withdraw')}>Withdraw</button>
      <BuyButton />
      <button onClick={() => handleButtonClick('Sell')}>Sell</button>
    </div>
  );
};

export default HeaderButtons;
