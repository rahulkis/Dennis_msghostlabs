import { useEffect, useState } from 'react'
import { Select } from 'antd'
import { connect } from 'react-redux'
import { ICategory } from '../../../../../../helpers/hierarchicalCategories'

const { Option } = Select

const ExcludedCategories = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    productCategories,
    excludedCategoriesStrings,
  } = props

  const { label } = excludedCategoriesStrings

  const [excludedCategories, setExcludedCategories] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: 'excludedCategories',
      extra: '',
    }) || []
  )

  const [options, setOptions] = useState<any>('')

  useEffect(() => {
    setExcludedCategories(
      getPropValue({
        styling,
        id,
        target,
        style: 'excludedCategories',
        extra: '',
      }) || []
    )
  }, [id])

  useEffect(() => {
    if (productCategories !== undefined && productCategories.length > 0) {
      let allOptions = productCategories.map((cat: ICategory) => {
        return (
          <Option key={cat.term_id} value={cat.slug}>
            {cat.name}
          </Option>
        )
      })
      setOptions(allOptions)
    }
  }, [productCategories])

  return (
    <div className="excluded-category">
      <label htmlFor="excluded-category">{label}:</label>
      <Select
        placeholder="None"
        mode="multiple"
        allowClear={true}
        style={{ width: '100%' }}
        defaultValue={excludedCategories}
        onChange={(value: string) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              excludedCategories: value,
            },
          })
          setExcludedCategories(value)
        }}
      >
        {options}
      </Select>
    </div>
  )
}

const mapStateToProps = (store: any) => ({
  productCategories: store.products.categories,
  excludedCategoriesStrings:
  store.i18n.backend.styling_and_option_controls.show_options
    .excluded_categories,
})

export default connect(mapStateToProps)(ExcludedCategories)
