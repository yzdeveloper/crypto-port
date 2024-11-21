// helpers
export function normalize(value, after) {      
        let groups= value.match(/^([+-]?)([0-9]+)(.[0-9]+)?$/);
        if (groups) {
            let minus = '';
            if (groups.length > 1 && groups[1] == '-') {
                minus = '-';
            }
            
            let three = '';
            if (groups.length > 3 && groups[3] && groups[3].length > 0) {
                let gthree = groups[3]; 
                if (gthree.length > (after + 1))
                {
                    gthree = gthree.substring(0, after + 1);
                }

                three = gthree.padEnd(after + 1, '0');
            } else {
                three = `.${''.padEnd(after, '0')}`;
            }

            // console.log('normalize:', groups[3], three);

            return `${minus}${groups[2]}${three}`;
        }

        return '0.00';
    }

export  function updateTickerPrice(ticker, callBack) {
    axios.get(`https://api.exchange.coinbase.com/products/${ticker}-USD/ticker`, 
        { 
            transformRequest: (data, headers) => {
                delete headers["X-Requested-With"];
                return data;
            } 
        })
        .then(response => {
            console.log('Ticker: ', ticker, response.data);
            callBack(response.data['ask'], response.data['bid']);
        })
        .catch(error => {
            console.error(`Error fetching ticker: ${ticker}`, error);
        });
}
