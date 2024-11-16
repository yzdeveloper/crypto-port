import React, { useState, useEffect } from 'react';

function ModalButton(props) {
  const [isOpen, setIsOpen] = useState(false); // Popup visibility state
  const [inputValue, setInputValue] = useState(''); // The value of the input box
  const [buttonLabel, setButtonLabel] = useState('Submit'); // Dynamic button label
  const [errorMessage, setErrorMessage] = useState(''); // Error message for validation

  const upperLimit = 1000; // Upper limit for the float value
  const text = props.text;
 

  // Open the popup with a specific action for the button (optional dynamic behavior)
  const openPopup = (label, action) => {
    console.log("openPopup ++")
    setButtonLabel(label);
    setAction(action);
    setIsOpen(true);
    console.log("openPopup --")
  };

  // The function to execute when the button is clicked
  const [action, setAction] = useState(() => () => {});

  const handleInputChange = (event) => {
    const value = event.target.value;
    if (value === '' || /^[0-9]*\.?[0-9]*$/.test(value)) { // Allow only numeric and float input
      setInputValue(value);
      setErrorMessage('');
    }
  };

  const handleSubmit = () => {
    const floatValue = parseFloat(inputValue);

    // Validate if the value is a positive float and less than the upper limit
    if (isNaN(floatValue) || floatValue <= 0 || floatValue > upperLimit) {
      setErrorMessage(`Please enter a positive float value less than or equal to ${upperLimit}`);
    } else {
      // Perform the action on the button (e.g., submit or save data)
      action(floatValue);
      closePopup();
    }
  };

  const closePopup = () => {
    setIsOpen(false);
    setInputValue(''); // Reset the input value
  };

  // Close the popup when clicking outside of it
  const handleClickOutside = (event) => {
    const modal = document.getElementById('popup-modal');
    if (modal && !modal.contains(event.target)) {
      closePopup();
    }
  };

  // Attach the click event listener for outside clicks
  useEffect(() => {
    console.log('isOpen effect')
    if (isOpen) {
      document.addEventListener('click', handleClickOutside);
    } else {
      document.removeEventListener('click', handleClickOutside);
    }
    return () => {
      document.removeEventListener('click', handleClickOutside);
    };
  }, [isOpen]);

  return (
    <div className="AddButton">
      {/* Button to open the popup */}
      <button onClick={() => openPopup('Submit', (value) => console.log('Action triggered with:', value))}>
        {text}
      </button>

      {/* Modal Popup */}
      {isOpen && (
        <div id="popup-modal" className="modal">
          <div className="modal-content">
            <button className="close" onClick={closePopup}>
              &times;
            </button>
            <h2>Enter a Positive Float</h2>
            <input
              type="text"
              value={inputValue}
              onChange={handleInputChange}
              placeholder="Enter a number"
              className="number-input"
            />
            {errorMessage && <p className="error">{errorMessage}</p>}

            <div className="modal-footer">
              <button onClick={handleSubmit}>{buttonLabel}</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default ModalButton;
