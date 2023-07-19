import { useEffect, useState } from 'react'
import { Select, TreeSelect } from 'antd'

// Redux
import { bindActionCreators, Dispatch } from 'redux'
import { connect } from 'react-redux'

// helper
import getHierarchicalCategories, { ICategory } from 'helpers/hierarchicalCategories'

import { orderFormActions } from 'store/actions'

const { setSelectedCategory, setFilteredCategories, setExcludedCategories } =
        orderFormActions

const { Option } = Select
const { SHOW_PARENT } = TreeSelect

const CategoryFilter = (props : any) => {
  const {
          products,
          style,
          getPropValue,
          properties,
          actions,
          categoryFilterStrings
        } = props

  const { categories } = products

  const { setSelectedCategory, setFilteredCategories, setExcludedCategories } =
          actions

  const { placeholder_text } = categoryFilterStrings

  const [placeholder, setPlaceholder] = useState(
    getPropValue({ properties, prop: 'placeholder' }) ?? placeholder_text
  )

  const [defaultCategory, setDefaultCat] = useState(
    getPropValue({
      properties,
      prop: 'defaultCategory'
    })
  ) // Default category. Value is in slug

  const [includedCategories, setIncludedCats] = useState(
    getPropValue({
      properties,
      prop: 'includedCategories'
    })
  ) // List of included categories. Array of slugs

  const [excludedCategories, setExcludedCats] = useState(
    getPropValue({
      properties,
      prop: 'excludedCategories'
    })
  ) // List of excluded categories. Array of slugs

  const [dropdownData, setdropdownData] = useState<any>('') // For hierarchal dropdown
  const [categoryDataList, setCategoryDataList] = useState([]) // List of categories non-hierarchal/no children
  const [defaultValue, setDefaultValue] = useState<any>('') // The default category name

  useEffect(() => {
    const text = getPropValue({ properties, prop: 'placeholder' })

    if (text !== null && text !== '') {
      setPlaceholder(text)
    } else {
      setPlaceholder(placeholder_text)
    }

    setDefaultCat(
      getPropValue({
        properties,
        prop: 'defaultCategory'
      })
    )
    setIncludedCats(
      getPropValue({
        properties,
        prop: 'includedCategories'
      })
    )
    setExcludedCats(
      getPropValue({
        properties,
        prop: 'excludedCategories'
      })
    )
  }, [properties])

  useEffect(() => {
    setSelectedCategory(defaultCategory)
  }, [defaultCategory])

  let filteredCategory : any = []

  // Included - Excluded
  if (includedCategories !== null && includedCategories.length > 0) {
    filteredCategory = includedCategories.filter((val : any) => excludedCategories?.includes(val) ?? true)
  }

  useEffect(() => {
    setExcludedCategories(excludedCategories)

    let treeData : any[] = []

    if (categories !== undefined && categories.length > 0) {
      treeData = getHierarchicalCategories(categories)

      // Append beginning
      setdropdownData(
        [
          {
            title   : placeholder,
            value   : placeholder,
            children: []
          }
        ].concat(treeData)
      )
    }
  }, [categories, includedCategories, excludedCategories])

  useEffect(() => {
    let catData : any = []

    if (categories !== undefined && categories.length > 0) {
      catData = getHierarchicalCategories(categories)
    }
    setCategoryDataList(catData)
  }, [categories, includedCategories, excludedCategories])

  useEffect(() => {
    setFilteredCategories(filteredCategory)

    if (filteredCategory.length > 0) {
      if (filteredCategory?.includes(defaultCategory)) {
        setDefaultValue(categoryDataList[defaultCategory])
      } else {
        setDefaultValue('')
      }
    } else if (typeof categoryDataList[defaultCategory] !== 'undefined') {
      setDefaultValue(categoryDataList[defaultCategory])
    } else {
      setDefaultValue('')
    }
  }, [
    defaultCategory,
    categoryDataList,
    includedCategories,
    excludedCategories
  ])

  let extraProps = {}
  if (defaultValue !== '') {
    extraProps = { value: defaultValue }
  }

  // Included - Excluded Categories
  // Show normal dropdown
  if (filteredCategory.length > 0) {
    // filteredCategory = [placeholder].concat(filteredCategory);

    let allOptions = filteredCategory.map((slug : string) => {
      const category = categories.find((cat : ICategory) => cat.slug === slug)

      return (
        <Option key={slug} value={slug}>
          {category.name}
        </Option>
      )
    })

    return (
      <Select
        className="wwof-category-filter"
        size="large"
        { ...extraProps }
        showSearch={ true }
        placeholder={ placeholder }
        allowClear={ true }
        style={ { width: '250px', ...style } }
        onChange={ (slug : any) => {
          // localStorage.removeItem("rowsHeight");
          setDefaultCat(slug)
        } }
      >
        { allOptions }
      </Select>
    )
  } else {
    // Show hierarchal dropdown
    return (
      <>
        <TreeSelect
          size="large"
          { ...extraProps }
          showSearch
          allowClear
          className="wwof-category-filter"
          treeData={ dropdownData }
          placeholder={ placeholder }
          treeDefaultExpandAll
          showCheckedStrategy={ SHOW_PARENT }
          style={ { width: '250px', ...style } }
          onChange={ (value) => {
            // localStorage.removeItem("rowsHeight");
            setDefaultCat(value)
          } }
        />
      </>
    )
  }
}

const mapStateToProps = (store : any) => ( {
  orderForm            : store.orderForm,
  products             : store.products,
  styling              : store.styling,
  dragAndDrop          : store.dragAndDrop,
  categoryFilterStrings:
  store.i18n.backend.header_footer_elements.category_filter
} )

const mapDispatchToProps = (dispatch : Dispatch) => ( {
  actions: bindActionCreators(
    {
      setSelectedCategory,
      setFilteredCategories,
      setExcludedCategories
    },
    dispatch
  )
} )

export default connect(mapStateToProps, mapDispatchToProps)(CategoryFilter)
