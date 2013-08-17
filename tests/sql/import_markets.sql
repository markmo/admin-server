load data local
infile '../fixtures/markets.csv'
into table market
fields terminated by ','
lines terminated by '\r'
ignore 1 lines
(market_id, market_name, external_id, status, event_id)