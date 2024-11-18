import React, { useState, useEffect } from 'react';

import ActionButtons from './ActionButtons';
import PortfolioPerformance from './PortfolioPerformance';
import PortfolioTable from './PortfolioTable';

function Portfolio() {
    const [portfolio, setPortfolio] = useState([]);
    const [cash, setCash] = useState(0.);
    const [cashAdd, setCashAdd] = useState(0.);

    const [invested, setInvested] = useState(0);
    const [releasedPnL, setReleasedPnL] = useState(0);
    const [unreleasedPnL, setUnreleasedPnL] = useState(0);

    function getInvested(portfolio) {
        let sum = 0.
        for (inst of portfolio) {
            sum += inst.buyPrice * inst.quantiry;
        }

        return sum;
    }

    function updateCash() {
        useEffect(() => {
            fetch('/api/cash')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(response.statusText);
                    }

                    return response.text();
                })
                .then(cashStr => {        
                    setCash(parseFloat(cashStr));
                })
                .catch(error => console.error('Error fetching data:', error));
        }, []);
    }

    updateCash();


    // Fetch from the backend
    useEffect(() => {
        fetch('/api/load_portfolio')
            .then(response => response.json())
            .then(port => {
                setPortfolio(port.porfolio);
                setInvested(port.cash + getInvested(port.porfolio));
                setReleasedPnL(port.releasedPnL);
                setUnreleasedPnL(0);
            })
            .catch(error => console.error('Error fetching data:', error));
    }, []);

    function normalize(value, after) {        
        let groups= value.match(/^([+-]?)([0-9]+)(.[0-9]+)?$/);
        if (groups) {
            let minus = '';
            if (groups[1] == '-') {
                minus = '-';
            }
            
            let three = '';
            if (groups[3].length > 0) {
                three = groups[3];
            } 

            three = three.padEnd(after, '0');

            return `${minus}${groups[2]}${three}`;
        }

        return '0.00';
    }

    function addCash(value) {
        if (isNaN(Number(value))) {
            console.log(`${value} is not a number`);
        }

        fetch(`/api/addCash?value=${value}`, {
            method: 'POST' })
        .then(response => {
            if (!response.ok) {
                throw new Error(response.statusText);
            }
            return response.text();
        })
        .then(cashStr => {   
            console.log(`cashStr=${cashStr}`);                 
            setCash(normalize(cashStr, 2));
        })
        .catch(error => console.error('Error fetching data:', error));
    }

    function onClickAdd() {
        addCash(cashAdd);
    }

    function onClickWithdraw() {
        addCash(-cashAdd);
    }

    return (
        <div className='Portfolio'>
            <div class='cashBox'>
            <div class='horizontal'>
                <label class='label'>Cash</label>
                <input class='number-input' type='text' value={cash} readOnly />
                <div class='bordered horizontal'>
                    <input class='number-input' type='number' inputMode='decimal' 
                        placeholder='0.0' step='0.01' min='0' value={cashAdd} 
                        onInput={e => {
                            let value = e.target.value;
                            if (/^([0-9]+.?[0-9]{0,2})$/.test(value)) {
                                if (/^(0[0-9]+.?[0-9]{0,2})$/.test(value)) {
                                    value = value.substring(1);
                                }

                                setCashAdd(value)
                            } else {
                                e.stopPropagation();
                            }
                        } }  
                        title='Enter cash value to add or to withdraw.' />
                    <div class='vertical'> 
                        <button class='cash-button' onClick={onClickAdd}>Add</button>
                        <button  class='cash-button' onClick={onClickWithdraw}>Withdraw</button>
                    </div>
                </div>
            </div>
            </div>  
        <ActionButtons />
        <PortfolioPerformance 
            invested={invested} 
            releasedPnL={releasedPnL} 
            unreleasedPnL={0} 
        />
        <PortfolioTable data={portfolio} />
        </div>
    );
}

export default Portfolio;

