import { useState, useEffect } from 'react'
import { TreeSelect, Select } from 'antd'
import { bindActionCreators, Dispatch } from 'redux'
import { connect } from 'react-redux'

// Actions
import { orderFormDataActions } from 'store/actions'

// helper
import useFormTableData from 'helpers/useFormTableData'
import getHierarchicalCategories, { ICategory } from 'helpers/hierarchicalCategories'

const { Option } = Select
const { SHOW_PARENT } = TreeSelect

const { setFormFilters } = orderFormDataActions

const CategoryFilter = (props: any) => {
  const {
    getPropValue,
    properties,
    orderFormId,
    fetchProducts,
    submitOnChange,
    defaultCategory,
    includedCategories,
    excludedCategories,
    orderForm,
    orderFormData,
    styles,
    actions,
    categoryFilterStrings
  } = props

  const { fetching, fetchingVariations } = useFormTableData({
    orderFormData,
    orderFormId
  })

  const { setFormFilters } = actions
  const { categories } = orderForm

  const formSettings = orderFormData?.formSettings?.[orderFormId],
    searchInput = formSettings?.['searchInput'] ?? ''

  const [dropdownData, setdropdownData] = useState<any>('') // For hierarchal dropdown
  const [categoryDataList, setCategoryDataList] = useState([]) // List of categories non-hierarchal/no children
  const [selectedValue, setSelectedValue] = useState<any>(formSettings?.selected_category ?? '') // The default category name

  let placeholder = getPropValue({ properties, prop: 'placeholder' }) ?? categoryFilterStrings?.placeholder
  placeholder = placeholder ? placeholder : categoryFilterStrings?.placeholder

  let filteredCategory: any = []

  if (includedCategories !== null && includedCategories.length > 0) {
    filteredCategory = includedCategories.filter((val: any) =>
      excludedCategories !== null ? !excludedCategories.includes(val) : true
    )
  }

  // Setting Hierarchical and one level array of categories
  useEffect(() => {
    let treeData: any[] = []

    if (categories !== undefined && categories.length > 0) {
      treeData = getHierarchicalCategories(categories).filter(catObj => excludedCategories === null ||
        !excludedCategories.includes(catObj.value))

      // Append beginning
      setdropdownData(
        [
          {
            title: placeholder,
            value: '',
            children: []
          }
        ].concat(treeData)
      )
    }
  }, [categories])

  useEffect(() => {
    let catData: any = []

    if (categories !== undefined && categories.length > 0) {
      catData = getHierarchicalCategories(categories)
    }
    setCategoryDataList(catData)
  }, [categories])

  // Setting Selected Value
  useEffect(() => {
    if (Object.keys(categoryDataList).length === 0) {
      return
    }
    if (filteredCategory.length > 0) {
      if (filteredCategory.includes(defaultCategory)) {
        setSelectedValue(categoryDataList[defaultCategory])
        setFormFilters({
          [orderFormId]: {
            ...orderFormData,
            selectedCategory: defaultCategory
          }
        })
      } else {
        setFormFilters({
          [orderFormId]: {
            ...orderFormData,
            selectedCategory: '',
            categoryComponentLoaded: true
          }
        })
      }
    } else if (
      typeof defaultCategory !== 'undefined' &&
      typeof categoryDataList[defaultCategory] !== 'undefined'
    ) {
      setSelectedValue(categoryDataList[defaultCategory])
      setFormFilters({
        [orderFormId]: {
          ...orderFormData,
          selectedCategory: defaultCategory
        }
      })
    } else {
      setFormFilters({
        [orderFormId]: {
          ...orderFormData,
          selectedCategory: '',
          categoryComponentLoaded: true
        }
      })
    }
  }, [categoryDataList, includedCategories, excludedCategories])

  const onChange = (categoryName: string, treeData: Array<any>) => {
    try {
      if (categoryName) {
        setSelectedValue(categoryName)
        setFormFilters({
          [orderFormId]: {
            ...orderFormData,
            selectedCategory: categoryName
          }
        })
      } else {
        setSelectedValue('')
        setFormFilters({
          [orderFormId]: {
            ...orderFormData,
            selectedCategory: ''
          }
        })
      }

      if (submitOnChange) {
        fetchProducts({
          orderFormData,
          search: searchInput,
          category: categoryName,
          active_page: 1,
          searching: 'yes'
        })
      }
    } catch (e) {
      console.log(e)
    }
  }

  // Included - Excluded Categories
  // Show normal dropdown
  if (filteredCategory.length > 0 && categories.length > 0) {
    // filteredCategory = [placeholder].concat(filteredCategory);
    let allOptions = filteredCategory.map((slug: string, index: number) => {
      const category = categories.find((cat: ICategory) => cat.slug === slug)
      return (
        <Option key={slug} value={slug}>
          {category.name}
        </Option>
      )
    })
    return (
      <Select
        size="large"
        value={selectedValue}
        showSearch={true}
        placeholder={placeholder}
        allowClear={true}
        style={{
          width: '250px',
          ...styles
        }}
        onChange={(slug: any) => {
          onChange(slug, dropdownData)
        }}
        onClear={() => setSelectedValue('')}
        disabled={fetching || fetchingVariations ? true : false}
      >
        {allOptions}
      </Select>
    )
  } else {
    return (
      <>
        <TreeSelect
          size="large"
          value={selectedValue}
          showSearch
          allowClear
          className="wwof-category-filter"
          treeData={dropdownData}
          placeholder={placeholder}
          treeDefaultExpandAll
          showCheckedStrategy={SHOW_PARENT}
          style={{
            width: '250px',
            ...styles
          }}
          onChange={(val: string) => onChange(val, dropdownData)}
          disabled={fetching || fetchingVariations ? true : false}
        />
      </>
    )
  }
}

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
  filter: store.filter,
  categoryFilterStrings: store.i18n.frontend.category_filter
})

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setFormFilters
    },
    dispatch
  )
})

export default connect(mapStateToProps, mapDispatchToProps)(CategoryFilter)
