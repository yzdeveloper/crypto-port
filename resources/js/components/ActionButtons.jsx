import React from 'react';
import ModalButton from './ModalButton'

const ActionButtons = () => {
  const handleButtonClick = (action) => {
    console.log(`${action} button clicked`);
    // Here you could trigger API calls to the backend based on the action
  };

  const addCash = () => {
    console.log(`Add cash.`);
  }
  return (
    <div className="header-buttons">
      <ModalButton text='Add' submit={() => addCash()} />
      <button onClick={() => handleButtonClick('Withdraw')}>Withdraw</button>
      <button onClick={() => handleButtonClick('Buy')}>Buy</button>
      <button onClick={() => handleButtonClick('Sell')}>Sell</button>
    </div>
  );
};

export default ActionButtons;