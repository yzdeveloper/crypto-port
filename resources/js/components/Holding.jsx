import React, { useState, useEffect, useRef } from 'react';
import { normalize, updateTickerPrice } from './utils';


// Modal component
const Holding = ({ index, row, onSell, onPriceChanged, sendTimerInterval }) => {
    const [currentPrice, setCurrentPrice] = useState(0);
    const currentPriceRef = useRef(currentPrice);
    const [sellQuantity , setSellQuantity ] = useState(0);
    const sellQuantityRef = useRef(sellQuantity);
    const [isSellDisabled , setIsSellDisabled ] = useState(true);

    useEffect(() => {
        currentPriceRef.current = currentPrice;
    }, [currentPrice]);

    useEffect(() => {
        sellQuantityRef.current = sellQuantity;
    }, [sellQuantity]);

    const updateTickerPriceCallBack = (a,b) => { 
        // console.log('onPriceChanged:', onPriceChanged, b);   
        setCurrentPrice(b);     
        onPriceChanged(index, b);
    };

    function onTimeToUpdateTickerPrice() {
        let st = row.instrument;
        updateTickerPrice(st, updateTickerPriceCallBack)
    }

    useEffect(() => {
        const intervalId = setInterval(onTimeToUpdateTickerPrice, 1000);  
        sendTimerInterval(intervalId);
        return () => clearInterval(index, intervalId);
      }, [row]);


    function updateSellQuantity(value) {
        setSellQuantity(value);
        let sq = Number(value);
        let q = Number(row.quantity);
        console.log('updateSellQuantity', sq, q);
        setIsSellDisabled((q === 0) || (sq === 0) || (sq > q) );
    }

    function getCurrentPrice(index) {
        return currentPrice;
    }

    function getCurrentValue() {
        let cp = getCurrentPrice();
        if (cp)
        {
            return normalize(`${Number(cp)*Number(row.quantity)}`, 2);
        }

        return null;
    }

    function getPnl(index) {
        let cv = getCurrentValue();
        if (cv)
        {
            return normalize(`${Number(cv) - Number(getValue())}`, 2) ; 
        }

        return  null;
    }

    function getValue() {
        return normalize(`${Number(row.price) * Number(row.quantity)}`, 2);
    }

    function onClick() {
        console.log('onClick', index, sellQuantityRef.current, currentPriceRef.current);
        onSell(index, sellQuantityRef.current, currentPriceRef.current);
    }


    return (
        <tr key={index}>
            <td>{row.instrument}</td>
            <td>{row.quantity}</td>
            <td>{row.price}</td>
            <td>{getValue(index) }</td>
            <td>{getCurrentPrice(index)}</td>
            <td>{getCurrentValue(index)}</td>
            <td>{getPnl(index)}</td>
            <td>
            <input className='number-input' type='number' inputMode='decimal' 
                placeholder='0.0' min='0' value={sellQuantity} 
                onInput={e => {
                    let value = e.target.value;
                    if (/^([0-9]+.?[0-9]{0,2})$/.test(value)) {
                        if (/^(0[0-9]+.?[0-9]{0,2})$/.test(value)) {
                            value = value.substring(1);
                        }

                        updateSellQuantity(value);
                    } else {
                        e.stopPropagation();
                    }
                } }  
                title='Enter quantity to sell.' />
            </td>
            <td>
                <button className='buttons' onClick={onClick} disabled={isSellDisabled} >Sell</button>
            </td>
        </tr>
    );

}

export default Holding;
