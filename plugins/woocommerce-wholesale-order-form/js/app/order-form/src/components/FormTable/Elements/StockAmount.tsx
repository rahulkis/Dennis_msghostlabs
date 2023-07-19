import { FC } from 'react'
import { MehOutlined, SmileOutlined } from '@ant-design/icons'

export interface IStockAmount {
  product: any,
  stockAmount: number,
  stockFormat: string,
  i18nInStock: string,
  i18nLowStock: string,
  wcLowStockAmount?: number,
}

const StockAmount: FC<IStockAmount> = ({
  stockAmount,
  stockFormat,
  i18nLowStock,
  i18nInStock,
  product,
  wcLowStockAmount = 2
}) => {
  switch (stockFormat) {
    case 'low_amount':
      if ((product.low_stock_amount >= stockAmount) || (wcLowStockAmount >= stockAmount)) {
        return <><MehOutlined style={{ color: '#DBA617' }} /> {`${i18nLowStock.replace(
          '%d',
          `${stockAmount}`
        )}`}</>
      }
      return <><SmileOutlined style={{ color: '#0F834D' }} /> {`${i18nInStock}`}</>
    case 'no_amount':
      return <><SmileOutlined style={{ color: '#0F834D' }} /> {`${i18nInStock}`}</>
    case '':
    default:
      return <><SmileOutlined style={{ color: '#0F834D' }} /> {`${stockAmount} ${i18nInStock}`}</>
  }
}

export default StockAmount
