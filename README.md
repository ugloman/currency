## Description
Сервис получения курсов и кросс-курсов валют с сайта Центрального банка Российской Федерации https://www.cbr.ru

## Requirements
- Docker
- Docker-compose

## QuickStart
В корневом каталоге проекта выполнить команду:
```bash
docker-compose up -d
```
После запуска всех контейнеров, проект собирается около 1 минуты, после чего готов к работе.

## Usage
#### Host
```bash
127.0.0.1:80
```

#### Endpoint
```bash
GET /get_currency_rates_by_date?date=:date&currencyCode=:currencyCode&baseCurrencyCode=:baseCurrencyCode
```

#### URL Parameters

| Name               | Type     | Format	         | Comment 			  | 
| --------------     | -------- |--------          |-------- 			  |
| `date`      	     | `date`   | d.m.Y            |required        |
| `currencyCode`     | `string` | exactly length 3 |required			  |
| `baseCurrencyCode` | `string` | exactly length 3 |default - RUR   |

#### Example Response
```bash
{"rate":0.9738,"rateDifference":0.0041}
```
