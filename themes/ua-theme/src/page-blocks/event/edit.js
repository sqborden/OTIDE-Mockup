const { useState } = wp.element;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const {
  BaseControl,
  TextControl,
  Button,
  DateTimePicker,
  Flex,
  FlexItem,
  Popover,
  __experimentalInputControl,
  Dashicon,
  PanelBody,
  ToggleControl
} = wp.components;
import LinkPanel from '../../elements/link-panel';
import { __ } from '@wordpress/i18n';
import './editor.css';

export default function Edit({ attributes, setAttributes }) {
  const { allDay, displayTime, title, url, opensInNewTab, start, end, location } = attributes;

  //these are the attributes we'll pass to the linkPanel
  const linkAttributes = {
    url,
    opensInNewTab,
  };

  let defaultStartLocale = null;
  if (start !== '') {
    defaultStartLocale = new Date(start).toLocaleString();
  }

  let defaultEndLocale = null;
  if (end !== '') {
    defaultEndLocale = new Date(end).toLocaleString();
  }

  const [startLocale, setStartLocale] = useState(defaultStartLocale);
  const [endLocale, setEndLocale] = useState(defaultEndLocale);
  const [isVisiblePopoverStart, setIsVisiblePopoverStart] = useState(false);
  const [isVisiblePopoverEnd, setIsVisiblePopoverEnd] = useState(false);

  const togglePopoverStart = () => {
    setIsVisiblePopoverStart(!isVisiblePopoverStart);
  };

  const togglePopoverEnd = () => {
    setIsVisiblePopoverEnd(!isVisiblePopoverEnd);
  };

  const setStartDate = (val) => {
    const date = new Date(val);

    setAttributes({ start: date.toUTCString() });
    setStartLocale(date.toLocaleString());
  };

  const setEndDate = (val) => {
    const date = new Date(val);

    setAttributes({ end: date.toUTCString() });
    setEndLocale(date.toLocaleString());
  };

  const blockProps = useBlockProps({className: 'ua-block'});

  return (
    <>
      <div {...blockProps}>
        <InspectorControls>
          <PanelBody title="Event Settings">
            <ToggleControl
              label="Display Time"
              help='Toggle whether or not to include the time in the event content.'
              checked={displayTime}
              onChange={value => setAttributes({ displayTime: value })}
            />

            <ToggleControl
              label="All Day"
              help='Display "All Day" rather than a time range'
              checked={allDay}
              onChange={value => setAttributes({ allDay: value })}
            />
          </PanelBody>
        </InspectorControls>

        <BaseControl help={__('Event title')}>
          <BaseControl.VisualLabel>{__('Title')}</BaseControl.VisualLabel>
          <TextControl onChange={(val) => setAttributes({ title: val })} value={title} />
        </BaseControl>

        <Flex justify="space-between">
          <FlexItem>
            <BaseControl help={__('Event start date/time')}>
              <BaseControl.VisualLabel>{__('Start')}</BaseControl.VisualLabel>
              <__experimentalInputControl
                value={startLocale}
                onFocus={togglePopoverStart}
                readOnly
                suffix={
                  <Dashicon
                    onClick={togglePopoverStart}
                    icon="calendar-alt"
                    style={{ marginRight: '4px', cursor: 'pointer' }}
                  />
                }
              />
            </BaseControl>

            {isVisiblePopoverStart && (
              <Popover placement="overlay">
                <div className="components-popover__content__ua-block">
                  <Button label={__('close')} variant="link" style={{ float: 'right' }} onClick={togglePopoverStart}>
                    X
                  </Button>

                  <DateTimePicker
                    onChange={(val) => setStartDate(val)}
                    currentDate={start}
                    is12Hour={true}
                    __nextRemoveHelpButton={true}
                    __nextRemoveResetButton={true}
                  />
                </div>
              </Popover>
            )}
          </FlexItem>

          <FlexItem>
            <BaseControl help={__('Event end date/time')}>
              <BaseControl.VisualLabel>{__('End')}</BaseControl.VisualLabel>

              <__experimentalInputControl
                value={endLocale}
                onFocus={togglePopoverEnd}
                readOnly
                suffix={
                  <Dashicon
                    onClick={togglePopoverEnd}
                    icon="calendar-alt"
                    style={{ marginRight: '4px', cursor: 'pointer' }}
                  />
                }
              />
            </BaseControl>

            {isVisiblePopoverEnd && (
              <Popover placement="overlay">
                <div className="components-popover__content__ua-block">
                  <Button label={__('close')} variant="link" style={{ float: 'right' }} onClick={togglePopoverEnd}>
                    X
                  </Button>

                  <DateTimePicker
                    onChange={(val) => setEndDate(val)}
                    currentDate={end}
                    is12Hour={true}
                    __nextRemoveHelpButton={true}
                    __nextRemoveResetButton={true}
                  />
                </div>
              </Popover>
            )}
          </FlexItem>

          <FlexItem>
            <BaseControl help={__('Physical or virtual location')}>
              <BaseControl.VisualLabel>{__('Location')}</BaseControl.VisualLabel>

              <TextControl onChange={(val) => setAttributes({ location: val })} value={location} />
            </BaseControl>
          </FlexItem>
        </Flex>
      </div>

      <InspectorControls>
        <LinkPanel
          attributes={linkAttributes}
          label={__('event details link')}
          setAttributes={setAttributes}
          enableOpensInNewTab={true}
        ></LinkPanel>
      </InspectorControls>
    </>
  );
}
