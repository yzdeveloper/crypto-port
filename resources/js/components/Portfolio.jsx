import React, { useState, useEffect, useRef } from 'react';
import Holding from './Holding';
import { normalize, updateTickerPrice } from './utils';

function Portfolio() {
    // cash
    const [cash, setCash] = useState('');
    const cashRef = useRef(cash);    
    const [cashAdd, setCashAdd] = useState('');
    const cashAddRef = useRef(cashAdd);    
    const [isWithdrawDisabled, setIsWithdrawDisabled] = useState(true);
    const [isAddDisabled, setIsAddDisabled] = useState(true);

    // performance
    const [invested, setInvested] = useState(0);
    const [releasedPnL, setReleasedPnL] = useState(0);
    const [unreleasedPnL, setUnreleasedPnL] = useState(0);

    // buy
    const [tickers, setTickers] = useState([]);
    const [selectedTickerPrice, setSelectedTickerPrice] = useState([]);    
    const selectedTickerPriceRef = useRef(selectedTickerPrice);    
    const [selectedTicker, setSelectedTicker] = useState('');
    const selectedTickerRef = useRef(selectedTicker);    
    const [buyQuantity, setBuyQuantity] = useState('');
    const buyQuantityRef = useRef(buyQuantity);    
    const [buyValue, setBuyValue] = useState('');
    const buyValueRef = useRef(buyValue);    
    const [isBuyDisabled, setIsBuyDisabled] = useState(true);
    const [portfolioUpdated, setPortfolioUpdated] = useState(0);
    const [cashUpdated, setCashUpdated] = useState(0);

    // table
    const [holdings, setHoldings] = useState([]);
    const currentPnl = [];
    const [sorting, setSorting] = useState(new Map());
    const [instrumentHeader, setInstrumentHeader] = useState('Instrument');
    const [instrumentSorting, setInstrumentSorting] = useState('');
    const [timers, setTimers] = useState([]);
 
    // cash functions
    useEffect(() => {
        fetch('/api/cash')
            .then(response => {
                if (!response.ok) {
                    throw new Error(response.statusText);
                }

                return response.text();
            })
            .then(cashStr => {        
                updateCashAndButtons(cashStr);
            })
            .catch(e => { console.error('Error fetching data:', e); });
    }, [cashUpdated]);

    useEffect(() => {
        cashRef.current = cash;
    }, [cash]);

    useEffect(() => {
        cashAddRef.current = cashAdd;
    }, [cashAdd]);

    function updateWithdrawButton(c, a) {
        let nc = Number(c);
        let na = Number(a);
        setIsWithdrawDisabled((nc == 0) || (na == 0) || ((nc != 0) && (na > nc))  );
    }

    function upddateAddButton(c, a) {
        let nc = Number(c);
        let na = Number(a);
        setIsAddDisabled(na == 0);
    }

    function updateCashAndButtons(cashValue) {
        console.log('cashValue:', cashValue, typeof cashValue, normalize(cashValue, 2));
        setCash(normalize(cashValue, 2));
        updateWithdrawButton(cashValue, cashAddRef.current);
        upddateAddButton(cashValue, cashAddRef.current)
    }

    function updateCashAddAndButtons(cashAddValue) {
        setCashAdd(cashAddValue);
        updateWithdrawButton(cashRef.current, cashAddValue);
        upddateAddButton(cashRef.current, cashAddValue)
    }

    function addCash(value) {
        fetch(`/api/addCash?value=${value}`, {
            method: 'POST' })
        .then(response => {
            if (!response.ok) {
                throw new Error(response.statusText);
            }
            return response.text();
        })
        .then(cashStr => {   
            updateCashAndButtons(normalize(cashStr, 2));
        })
        .catch(error => console.error('Error adding cash:', error));
    }

    function onClickAdd() {
        console.log('onClickAdd', cashAddRef.current)
        addCash(cashAddRef.current);
    }

    function onClickWithdraw() {
        addCash(-cashAddRef.current);
    }

    // Buying
    useEffect(() => {
        axios.get('https://api.exchange.coinbase.com/products', 
            { 
                transformRequest: (data, headers) => {
                    delete headers["X-Requested-With"];
                    return data;
                } 
            })
            .then(response => {
                let data = response.data
                    .filter(x => { 
                        //console.log(x);
                        return !x['trading_disabled'] && (x['quote_currency'] === 'USD'); 
                    })
                    .sort((a, b) => {
                    let x1 = a['quote_currency'];
                    let x2 = a['base_currency']; 
                    let y1 = b['quote_currency'];
                    let y2 = b['base_currency']; 
                    return ((x1 < y1) ? -1 : 
                        ((x1 > y1) ? 1 : 
                            ((x2 < y2) ? -1 :
                                ((x2 > x2) ? 1 : 0) )));            
                });

                let tickers = data.map(v => v['base_currency']);
                setTickers(tickers);
            })
            .catch(error => {
                console.error("Error fetching tickers:", error);
            });
    }, []);

    useEffect(() => {
        selectedTickerRef.current = selectedTicker;
    }, [selectedTicker]);

    useEffect(() => {
        buyQuantityRef.current = buyQuantity;
    }, [buyQuantity]);

    useEffect(() => {
        selectedTickerPriceRef.current = selectedTickerPrice;
    }, [selectedTickerPrice]);

    useEffect(() => {
        buyValueRef.current = buyValue;
    }, [buyValue]);
    

    const updateTickerPriceCallBack = (a,b) => {
        setSelectedTickerPrice(a);
        updateBuyValue(buyQuantityRef.current, a); 
    }

    function onTimeToUpdateTickerPrice() {
        let st = selectedTickerRef.current;
        // console.log('onTimeToUpdateTickerPrice', st);
        if (st && st.length > 0) {
            updateTickerPrice(st, updateTickerPriceCallBack)
        }
    }



    useEffect(() => {
        const intervalId = setInterval(onTimeToUpdateTickerPrice, 1000);    
        return () => clearInterval(intervalId);
      }, []);

    function onTickerSelected(ticker) {
        setSelectedTicker(ticker);
        onTimeToUpdateTickerPrice();
    }

    function updateBuyValue(quantity, price) {
        // console.log('updateBuyValue:', quantity, price);
        let value = (Number(quantity) * Number(price)).toFixed(2);
        setBuyValue(value); 
        let cv = cashRef.current;
        // console.log('updateBuyValue2:', value, cashRef.current, (Number(value) == 0), (Number(value) > Number(cv)));
        setIsBuyDisabled((Number(value) == 0) || (Number(value) > Number(cashRef.current)));
    }

    function updatePortfolio() {
        setPortfolioUpdated(portfolioUpdated + 1);
    }

    function updateCash() {
        setCashUpdated(cashUpdated + 1);
    }

    function handlePurchase() {
        console.log(`Buying ${buyQuantityRef.current} of ${selectedTickerRef.current} at ${selectedTickerPriceRef.current} each`);
        let data = {
            instrument: selectedTickerRef.current,
            quantity: buyQuantityRef.current,
            price: selectedTickerPriceRef.current,
        };
        fetch('/api/holdings/bought', {
            method: 'PUT', 
            headers: {
                'Content-Type': 'application/json', 
              },
              body: JSON.stringify(data),         
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(response.statusText);
            }

            updatePortfolio();
            updateCash();
        })
        .catch(error => console.error('Error purchasing ticker:', error));
    };


    // Portfolio
    function getInvested(portfolio) {
        let sum = 0.
        for (let inst of portfolio) {
            sum += Number(inst.price) * Number(inst.quantity);
        }

        let ret = normalize(`${sum}`, 2);
        // console.log('getInvested:', ret); 
        return ret;
    }

    function getSorting() {
        let sortCommands = [];
        sorting.forEach(function(value, key) {
            if (['instrument', 'quantity', 'price'].includes(key)) {
                console.log('getSorting:', sortCommands, sortCommands.size);
                sortCommands.push((sortCommands.size == 0 || !sortCommands.size) ? '?' : '&');
                sortCommands.push(`sort_by=${key}|${value ? 'asc' : 'desc'}`);
            }
        });

        console.log('getSorting:', sortCommands);
        return sortCommands.join('');
    }

    function getFiltering(s) {
        if (instrumentSorting) {
            return `${(s && s.length > 0) ? '&' : '?' }instrument=${instrumentSorting}`;
        } else {
            return '';
        }
    }

    function updateTimers(porfolio) {
        for (let t of timers) {
            //clearInterval(t);
            console.log('updateTimers:', t); 
        }

        setTimers([]);
    }

    useEffect(() => {
        let s = getSorting();
        let f = getFiltering(s);
        fetch(`/api/holdings${s}${f}`)
            .then(response => response.json())
            .then(porfolio => {
                console.log('porfolio:', porfolio);
                setHoldings(porfolio);
                setInvested(getInvested(porfolio));
                updateTimers(porfolio);
            })
            .catch(error => console.error('Error fetching holdings:', error));
    }, [portfolioUpdated]);

    useEffect(() => {
        fetch('/api/pnl')
            .then(response => {
                if (!response.ok) {
                    throw new Error(response.statusText);
                }

                return response.text();
            })
            .then(pnlStr => {        
                setReleasedPnL(normalize(pnlStr, 2));
            })
            .catch(e => { console.error('Error fetching pnl:', e); });
    }, []);
 
    function onClickSell(index, q, p) {
        console.log(`Selling ${q} of ${holdings[index].instrument} at ${p} each`);
        let data = {
            instrument: holdings[index].instrument,
            quantity: q,
            price: p,
        };
        fetch('/api/holdings/sold', {
            method: 'PUT', 
            headers: {
                'Content-Type': 'application/json', 
              },
              body: JSON.stringify(data),         
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(response.statusText);
            }

            updatePortfolio();
            updateCash();
        })
        .catch(error => console.error('Error selling ticker:', error));
    }
        
    function onPriceChanged(index, price) {
        currentPnl[index] =  (Number(price) - Number(holdings[index].price) ) * Number(holdings[index].quantity);
        setUnreleasedPnL( normalize( `${currentPnl.reduce((partialSum, a) => partialSum + a, 0)}`, 2));
    }


    function backEndSort() {
        updatePortfolio();
    }

    function handleBackEndSortClick(column) {
        console.log('handleBackEndSortClick', column);
        if (sorting.has(column)) {
            console.log('A');
            let dir = sorting.get(column);
            if (dir) {
                // desc
                console.log('B');
                sorting.set(column, false);
            } else {
                // no sorting
                console.log('C');
                sorting.delete(column);    
            }
        } else {
            // asc
            sorting.set(column, true); 
        }
        
        // console.log('handleBackEndSortClick', sorting);
        setSorting(sorting);
        setInstrumentHeader(`Instrument ${sorting.has('instrument') ? (sorting.get('instrument') ? '↑' : '↓') : ''}`);                        

        backEndSort();        
    }

    function onTimerInterval(index, interval) {
        timers[index] = interval;
        setTimers(timers);
    }


    // render
    return (
        <div className='Portfolio'>
            {/* Cash */}
            <div className='box'>
                <div className='horizontal'>
                    <label className='label'>Cash</label>
                    <input className='number-input' type='text' value={cash} readOnly />
                    <div className='bordered horizontal'>
                        <input className='number-input' type='number' inputMode='decimal' 
                            placeholder='0.0' step='0.01' min='0' value={cashAdd} 
                            onInput={e => {
                                let value = e.target.value;
                                if (/^([0-9]+.?[0-9]{0,2})$/.test(value)) {
                                    if (/^(0[0-9]+.?[0-9]{0,2})$/.test(value)) {
                                        value = value.substring(1);
                                    }

                                    updateCashAddAndButtons(value);
                                } else {
                                    e.stopPropagation();
                                }
                            } }  
                            title='Enter cash value to add or to withdraw.' />
                        <div className='vertical'> 
                            <button className='buttons' onClick={onClickAdd} disabled={isAddDisabled} >Add</button>
                            <button  className='buttons' onClick={onClickWithdraw} disabled={isWithdrawDisabled} >Withdraw</button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Summary */}
            <div className='box bordered'>
                <div className='horizontal'>
                    <div className='vertical width'>
                        <label className='label'>Invested</label>
                        <input className='number-input' type='text' value={invested} readOnly />
                    </div>
                    <div className='vertical width'>
                        <label className='label'>Released PnL</label>
                        <input className='number-input' type='text' value={releasedPnL} readOnly />
                    </div>
                    <div className='vertical width'>
                        <label className='label'>Unreleased PnL</label>
                        <input className='number-input' type='text' value={unreleasedPnL} readOnly />
                    </div>
                </div>
            </div>

            {/* Buy */}
            <div className='box bordered'>
                <div className='horizontal'>
                    <div className='width'>
                        <label className='right-margin' >Ticker and Price</label>
                        <select 
                            className='number-input' 
                            value={selectedTicker} onChange={(e) => onTickerSelected(e.target.value)}>
                            <option value=''>Select a Ticker</option>
                            {tickers.map((ticker) => (
                            <option key={ticker} value={ticker}>
                                {ticker}
                            </option>
                            ))}
                        </select>
                    </div>

                    <div className='width'>
                        <label className='label'>Price</label>
                        <input
                            className='number-input' 
                            type="number"
                            value={selectedTickerPrice}
                            readOnly
                        />
                    </div>

                    <div className='width'>
                        <label className='label'>Quantity</label>
                        <input
                            className='number-input' 
                            type="number"
                            value={buyQuantity}
                            onChange={(e) => setBuyQuantity(e.target.value)}
                            placeholder="Enter Quantity"
                            onInput={e => {
                                let value = e.target.value;
                                if (/^([0-9]+.?[0-9]{0,2})$/.test(value)) {
                                    if (/^(0[0-9]+.?[0-9]{0,2})$/.test(value)) {
                                        value = value.substring(1);
                                    }

                                    setBuyQuantity(value);
                                    updateBuyValue(value, selectedTickerPriceRef.current);
                                } else {
                                    e.stopPropagation();
                                }
                            } }  
                        />
                    </div>

                    <div className='width'>
                        <label className='label'>Value</label>
                        <input
                            className='number-input' 
                            type="number"
                            value={buyValue}
                            readOnly
                        />
                    </div>

                    <div className='width'>
                        <button className='buttons centered' onClick={handlePurchase} disabled={isBuyDisabled} >Buy</button>
                    </div>

                </div>
            </div>
            
            {/* Filtering */}
            <div className='box'>
                <input
                            className='number-input' 
                            type="text"
                            value={instrumentSorting}
                            placeholder="Instrument filtering"
                            onInput={e => {
                                    let value = e.target.value;
                                    setInstrumentSorting(value);
                                    updatePortfolio();
                                } 
                            }  
                        />                        
            </div>

            {/* Portfolio */}
            <table className="portfolio-table">
            <thead>
                <tr>
                    <th onClick={() => handleBackEndSortClick('instrument')} >
                        {instrumentHeader}
                    </th>
                    <th>Quantity</th>
                    <th>Bought Price</th>
                    <th>Bought Value</th>
                    <th>Current Price</th>
                    <th>Current Value</th>
                    <th>PnL</th>
                    <th>Sold quantity</th>
                    <th>Sell</th>
                </tr>
            </thead>
            <tbody>
                {holdings.map((row, index) => (
                    <Holding key={index} index={index} row={row} onSell={onClickSell} onPriceChanged={onPriceChanged} sendTimerInterval={onTimerInterval}  />
                ))}
            </tbody>
            </table>
        </div>
    );
}

export default Portfolio;

