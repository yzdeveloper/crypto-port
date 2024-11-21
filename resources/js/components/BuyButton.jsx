import React, { useState, useEffect } from 'react';
import axios from 'axios';

// Modal component
const BuyModal = ({ isOpen, onClose, onSubmit }) => {
  const [tickers, setTickers] = useState([]);
  const [selectedTicker, setSelectedTicker] = useState('');
  const [quantity, setQuantity] = useState('');
  const [value, setValue] = useState('');

  useEffect(() => {
    if (isOpen) {
      // Fetch data from third-party API (replace with actual API)
      axios.get('https://api.exchange.coinbase.com/products')
        .then(response => {
          setTickers(response.data);
        })
        .catch(error => {
          console.error("Error fetching tickers:", error);
        });
    }
  }, [isOpen]);

  const handleSubmit = () => {
    if (!selectedTicker || !quantity || !value) {
      alert("Please fill in all fields");
      return;
    }

    onSubmit(selectedTicker, quantity, value);
  };

  if (!isOpen) return null;

  return (
    <div className="modal-overlay">
      <div className="modal-content">
        <h2>Buy Stock</h2>
        <div>
          <label>Ticker and Price</label>
          <select value={selectedTicker} onChange={(e) => setSelectedTicker(e.target.value)}>
            <option value="">Select a Ticker</option>
            {tickers.map((ticker) => (
              <option key={ticker.ticker} value={ticker.ticker}>
                {ticker.ticker} - ${ticker.price}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label>Quantity</label>
          <input
            type="number"
            value={quantity}
            onChange={(e) => setQuantity(e.target.value)}
            placeholder="Enter Quantity"
          />
        </div>

        <div>
          <label>Value</label>
          <input
            type="number"
            value={value}
            onChange={(e) => setValue(e.target.value)}
            placeholder="Enter Value"
          />
        </div>

        <div>
          <button onClick={handleSubmit}>Buy</button>
          <button onClick={onClose}>Cancel</button>
        </div>
      </div>
    </div>
  );
};

// Parent component
const BuyButton = () => {
  const [isModalOpen, setIsModalOpen] = useState(false);

  const handleOpenModal = () => {
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
  };

  const handleBuySubmit = (ticker, quantity, value) => {
    // Perform buy action (submit to API or other logic)
    console.log(`Buying ${quantity} of ${ticker} at ${value} each`);

    // Close modal after submitting
    setIsModalOpen(false);
  };

  return (
    <div>
      <button onClick={handleOpenModal}>Buy</button>
      <BuyModal
        isOpen={isModalOpen}
        onClose={handleCloseModal}
        onSubmit={handleBuySubmit}
      />
    </div>
  );
};

export default BuyButton;
