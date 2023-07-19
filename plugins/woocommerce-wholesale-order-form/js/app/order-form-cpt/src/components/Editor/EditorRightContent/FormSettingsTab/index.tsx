import React, { useEffect } from 'react'
import { bindActionCreators, Dispatch } from 'redux'
import { connect } from 'react-redux'
import { Checkbox, Form, InputNumber, Popover, Radio, Select, Tooltip } from 'antd'
import { InfoCircleOutlined, LoadingOutlined } from '@ant-design/icons'

// Actions
import { orderFormActions } from 'store/actions'
import ProductsPerPage from './ProductsPerPage'

const { getOrderFormSettings, setOrderFormSettingsData } = orderFormActions

interface ISetSettings {
  type : string;
  key : string;
  value? : any[];
  event? : React.SyntheticEvent;
}

const DisplayOption = (props : any) => {
  const { item, orderForm, setOrderFormSettingsData, formSettingsTabStrings } =
          props

  const { additional_note, more_info } = formSettingsTabStrings

  const setSettingState = (props : ISetSettings) => {
    const { type, key, value, event } = props

    if (type === 'checkbox' && typeof event !== 'undefined') {
      let target = event.target as HTMLInputElement

      setOrderFormSettingsData({
        [key]: target.checked
      })
    } else {
      setOrderFormSettingsData({
        [key]: value
      })
    }
  }

  switch (item.type) {
    case 'radio':
      return (
        <div className={ `wwof-${ item?.id }-wrapper` }>
          <Form.Item
            className={ item.id }
            style={ { whiteSpace: 'unset' } }
            name={ item.id }
            label={ item.title }
          >
            <Radio.Group>
              { Object.keys(item['options']).map((data : any, index : any) => {
                return (
                  <Radio key={ index } value={ data }>
                    { item['options'][data] }
                  </Radio>
                )
              }) }
            </Radio.Group>
          </Form.Item>
        </div>
      )
    case 'checkbox':
      return (
        <div className={ `wwof-${ item?.id }-wrapper` }>
          <Form.Item name={ item.id } label={ item.title }>
            <>
              <Checkbox
                checked={ orderForm.settingsData[item?.id] ? true : false }
                onChange={ (event : any) =>
                  setSettingState({
                    type: 'checkbox',
                    key : item.id,
                    event
                  })
                }
              >
                { item.desc }{ ' ' }
                { typeof item.note !== 'undefined' ? (
                  <Popover
                    className="lazy-loading-note"
                    content={ () => (
                      <p
                        style={ { width: '300px' } }
                        dangerouslySetInnerHTML={ { __html: item.note } }
                      />
                    ) }
                    title={ additional_note }
                    trigger="click"
                  >
                    <Tooltip title={ more_info }>
                      <InfoCircleOutlined />
                    </Tooltip>
                  </Popover>
                ) : (
                  ''
                ) }
              </Checkbox>
              {/* Lazy Loading Products Per page */ }
              { item.id === 'lazy_loading' ? <ProductsPerPage /> : '' }
            </>
          </Form.Item>
        </div>
      )
    case 'number':
      return (
        <div className={ `wwof-${ item?.id }-wrapper` }>
          <Form.Item label={ item.title }>
            <InputNumber />
          </Form.Item>
        </div>
      )
    case 'select':
      return (
        <div className={ `wwof-${ item?.id }-wrapper` }>
          <Form.Item label={ item.title }>
            <Select
              placeholder={ item.placeholder }
              allowClear={ true }
              onChange={ (value : any) =>
                setSettingState({
                  type: 'select',
                  key : item.id,
                  value
                })
              }
              value={ orderForm.settingsData[item?.id] ?? '' }
            >
              { Object.keys(item['options']).map((data : any, index : any) => {
                return (
                  <Select.Option key={ index } value={ data }>
                    { item['options'][data] }
                  </Select.Option>
                )
              }) }
            </Select>
          </Form.Item>
        </div>
      )
    case 'multiselect':
      return (
        <div className={ `wwof-${ item?.id }-wrapper` }>
          <Form.Item label={ item?.title }>
            <Select
              id={ `wwof-${ item?.id }` }
              value={ orderForm.settingsData[item.id] }
              notFoundContent={ item.no_content }
              mode="multiple"
              placeholder={ item.placeholder }
              allowClear={ true }
              maxTagTextLength={ 50 }
              onChange={ (value : any) => {
                setSettingState({
                  type: 'select',
                  key : item.id,
                  value
                })
              } }
              // Filter based on the text displayed
              filterOption={ (input : string, option : any) =>
                option.children.toLowerCase().indexOf(input.toLowerCase()) >=
                0 ||
                option.children.toLowerCase().indexOf(input.toLowerCase()) >= 0
              }
            >
              { Object.keys(item.options).map((data : any, index : any) => {
                return (
                  <Select.Option
                    key={ item.options[index]['id'] }
                    value={ item.options[index]['id'] }
                  >
                    { item.options[index]['text'] }
                  </Select.Option>
                )
              }) }
            </Select>
            { item.desc }
          </Form.Item>
        </div>
      )
    case 'wwof_image_dimension':
      return (
        <div className={ `wwof-${ item?.id }-wrapper` }>
          <Form.Item label={ item.title }>
            <InputNumber value={ item['default'].width } />x
            <InputNumber value={ item['default'].height } />
            px
          </Form.Item>
        </div>
      )
    default:
      return <></>
  }
}

const DisplaySettings = (props : any) => {
  const { settings } = props

  const options = Object.keys(settings).map((index : any) => {
    const item = settings[index]

    return <DisplayOption item={ item } key={ index } { ...props } />
  })

  return Object.values(settings).length > 0 ? (
    <>{ options }</>
  ) : (
    <LoadingOutlined />
  )
}

const FormSettingsTab = (props : any) => {
  const { orderForm, formSettingsTabStrings } = props
  const { getOrderFormSettings, setOrderFormSettingsData } = props.actions

  const propsToPass = {
    settingsData: orderForm.settingsData,
    setOrderFormSettingsData,
    settings    : orderForm.settings,
    orderForm,
    formSettingsTabStrings
  }

  useEffect(() => {
    getOrderFormSettings()
  }, [])

  return <DisplaySettings { ...propsToPass } />
}

const mapStateToProps = (store : any, props : any) => ( {
  orderForm             : store.orderForm,
  formSettingsTabStrings: store.i18n.backend.form_settings_tab
} )
const mapDispatchToProps = (dispatch : Dispatch) => ( {
  actions: bindActionCreators(
    {
      getOrderFormSettings,
      setOrderFormSettingsData
    },
    dispatch
  )
} )

export default connect(mapStateToProps, mapDispatchToProps)(FormSettingsTab)
