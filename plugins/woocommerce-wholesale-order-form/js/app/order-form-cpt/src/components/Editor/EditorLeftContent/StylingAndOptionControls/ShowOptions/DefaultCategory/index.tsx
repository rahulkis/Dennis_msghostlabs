import { useEffect, useState } from 'react'
import { Select } from 'antd'
import { connect } from 'react-redux'
import { ICategory } from 'helpers/hierarchicalCategories'

const { Option } = Select

const DefaultCategory = (props: any) => {
  const {
    styling,
    id,
    target,
    getPropValue,
    productCategories,
    updateStyling,
    setStyles,
    getCategoryList,
    defaultCategoryStrings,
  } = props

  const [defaultValue, setDefaultValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: 'defaultCategory',
      extra: '',
    }) || []
  )

  const [options, setOptions] = useState<any>('')

  useEffect(() => {
    setDefaultValue(
      getPropValue({
        styling,
        id,
        target,
        style: 'defaultCategory',
        extra: '',
      })
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
    <div className="default-category">
      <label htmlFor="default-category">{defaultCategoryStrings?.label}:</label>
      <Select
        showSearch={true}
        placeholder="None"
        allowClear={true}
        style={{ width: '100%' }}
        defaultValue={defaultValue}
        onChange={(value: string) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              defaultCategory: value,
            },
          })
          setDefaultValue(value)
        }}
      >
        {options}
      </Select>
    </div>
  )
}

const mapStateToProps = (store: any) => ({
  productCategories: store.products.categories,
  defaultCategoryStrings:
  store.i18n.backend.styling_and_option_controls.show_options
    .default_category,
})

export default connect(mapStateToProps)(DefaultCategory)
