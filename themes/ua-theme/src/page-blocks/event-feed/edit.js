/* global wp */
const { useBlockProps, InspectorControls, BlockControls, AlignmentToolbar } = wp.blockEditor;
const { useEffect } = wp.element;
const { __experimentalNumberControl, FormTokenField, ToggleControl, PanelBody, RadioControl, SelectControl, TextControl } = wp.components;

function decodeEntities(str) {
  return typeof str === 'string'
    ? str.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"')
    : str;
}

export default function Edit({ attributes, setAttributes }) {
  const {
    audience,
    audiences,
    calendarURL,
    dateRange,
    departmentNames,
    departmentOrGroup,
    departments,
    departmentURLs,
    departmentValues,
    eventNum,
    events,
    eventsHeading,
    eventType,
    eventTypes,
    groupQuery,
    id,
    matchAll,
    maxColumns,
    multipleDepartments,
    multipleStudentGroups,
    singleDepartment,
    singleGroup,
    studentGroupNames,
    studentGroups,
    studentGroupURLs,
    studentGroupValues,
    textAlignment,
    topic,
    topics
  } = attributes;
  const inlineStyles = maxColumns === 0 ? undefined : { '--grid-column-count': attributes.maxColumns };
  const blockProps = useBlockProps({ className: 'ua_minerva', style: inlineStyles });
  const fullMonthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

  useEffect(() => {
    if (!id) {
      setAttributes({ id: blockProps.id });
    }

    if ((departmentValues.length && departmentValues.length <= 1)) {
      Promise.all([
        fetch('https://calendar.ua.edu/api/2/departments?pp=100').then(resp => resp.json()),
        fetch('https://calendar.ua.edu/api/2/departments?pp=100&page=2').then(resp => resp.json())
      ]).then((departmentsArray) => {
        const departments = [...departmentsArray[0].departments, ...departmentsArray[1].departments];

        setAttributes({
          departments: departments,
          departmentNames: departments.map((item) => {
            return item.department.name
          }),
          departmentValues: departmentValues.concat(
            departments.map((item) => {
              return { title: item.department.name, label: item.department.name, value: item.department.id }
            })
          ),
          // https://stackoverflow.com/questions/8348584/mapping-an-array-of-objects-to-key-value-pairs-in-coffeescript#answer-8349124
          departmentURLs: departments.reduce((array, item) => {
            array[item.department.id] = item.department.localist_url;
            return array;
          }, {})
        });
      });
    }

    if (studentGroupValues.length && studentGroupValues.length <= 1) {
      let groupsPromiseArray = [];

      fetch('https://calendar.ua.edu/api/2/groups?pp=100')
        .then(resp => resp.json())
        .then((value) => {
          if(value.page.total) {
            for(let i = 1; i <= value.page.total; i++) {
              groupsPromiseArray.push(fetch('https://calendar.ua.edu/api/2/groups?pp=100&page=' + i.toString()).then(resp => resp.json()))
            }

            Promise.all(groupsPromiseArray).then((groupsArray) => {
              let studentGroups = [];
              
              for(let i = 0; i < groupsArray.length; i++) {
                studentGroups.push(...groupsArray[i].groups);
              }

              setAttributes({
                studentGroups: studentGroups,
                studentGroupValues: studentGroupValues.concat(
                  studentGroups.map((item) => {
                    return { label: item.group.name, value: item.group.id }
                  })
                ),
                studentGroupNames: studentGroups.map((item) => {
                  return item.group.name
                }),
                studentGroupURLs: studentGroups.reduce((array, item) => {
                  array[item.group.id] = item.group.localist_url;
                  return array;
                }, {})
              });
            });
          }
      });
    }

    if (eventTypes && eventTypes.length <= 1) {
      fetch('https://calendar.ua.edu/api/2/events/filters/')
        .then(response => response.json())
        .then((filters) => {
          setAttributes({
            filters: filters,
            eventTypes: eventTypes.concat(filters.event_types.map((value) => {
              return { label: value.name, value: value.id }
            })),
            topics: topics.concat(filters.event_topic.map((value) => {
              return { label: value.name, value: value.id }
            })),
            audiences: audiences.concat(filters.event_target_audience.map((value) => {
              return { label: value.name, value: value.id }
            }))
          });

          updateFeed('range', dateRange);
        });
    }
  }, []);

  function updateFeed(updateType, data = '', filter = '') {
    let arrayQuery = '';
    let newGroupQuery = groupQuery ? groupQuery : '';
    let newMatch = matchAll ? '&require_all=true' : '&match=any';
    let newMultipleDepartments = multipleDepartments;
    let newMultipleStudentGroups = multipleStudentGroups;
    let newRange = dateRange;
    let query = '';

    if (updateType === 'type') {
      if (data) {
        arrayQuery = arrayQuery + '&type[]=' + data;
      }

      if (filter === 'event_type') {
        query = arrayQuery + '&type[]=' + audience + '&type[]=' + topic;
      } else if (filter === 'audience') {
        query = '&type[]=' + eventType + arrayQuery + '&type[]=' + topic;
      } else if (filter === 'topic') {
        query = '&type[]=' + eventType + '&type[]=' + audience + arrayQuery;
      }
    } else {
      let filters = [];

      if (eventType) {
        filters = filters.concat(eventType);
      }

      if (audience) {
        filters = filters.concat(audience);
      }

      if (topic) {
        filters = filters.concat(topic)
      }

      if (filters.length) {
        filters.forEach(function (item) {
          if (item !== '') {
            query = query + '&type[]=' + item;
          }
        });
      }
    }

    switch(updateType) {
      case 'departmentFormField':
        newGroupQuery = handleFormField(newGroupQuery, data, departments, multipleStudentGroups, 'department'); 
        break;
      case 'departmentOrGroup':
        if (data === 'department') {
          newGroupQuery = '&group_id[]=' + singleDepartment;
          setAttributes({ calendarURL: departmentURLs[singleDepartment] });
        } else if (data === 'group') {
          newGroupQuery = '&group_id[]=' + singleGroup;
          setAttributes({ calendarURL: studentGroupURLs[singleGroup] });
        }

        setAttributes({ departmentOrGroup: data });
        setAttributes({ groupQuery: newGroupQuery });
        break;
      case 'matchAll':
        setAttributes({ matchAll: data });

        if (data) {
          newMatch = '&require_all=true';

          if (departmentOrGroup === 'department') {
            newGroupQuery = '&group_id[]=' + singleDepartment;
            setAttributes({ calendarURL: departmentURLs[singleDepartment] });
          } else if (departmentOrGroup === 'group') {
            newGroupQuery = '&group_id[]=' + singleGroup;
            setAttributes({ calendarURL: studentGroupURLs[singleGroup] });
          }

          setAttributes({ groupQuery: newGroupQuery });
        } else {
          newMatch = '&match=any';
          newGroupQuery = '';

          if (multipleDepartments) {
            multipleDepartments.forEach((item) => {
              newGroupQuery += '&group_id[]=' + item;
            });
          } else if (multipleStudentGroups) {
            multipleStudentGroups.forEach((item) => {
              newGroupQuery += '&group_id[]=' + item;
            });
          }

          setAttributes({ groupQuery: newGroupQuery });
          setAttributes({ calendarURL: '' });
        }
        break;
      case 'multipleDepartments':
        newGroupQuery = handleMultiple(newGroupQuery, data, newMultipleDepartments, multipleStudentGroups, updateType);
        break;
      case 'multipleStudentGroups':
        newGroupQuery = handleMultiple(newGroupQuery, data, newMultipleStudentGroups, multipleDepartments, updateType);
        break;
      case 'range':
        newRange = data;
        setAttributes({ dateRange: data });
        break;
      case 'singleDepartment':
        newGroupQuery = '&group_id[]=' + data;
        setAttributes({ singleDepartment: data });
        setAttributes({ groupQuery: newGroupQuery });
        setAttributes({ calendarURL: departmentURLs[data] });
        break;
      case 'singleGroup':
        newGroupQuery = '&group_id[]=' + data;
        setAttributes({ singleGroup: data });
        setAttributes({ groupQuery: newGroupQuery });
        setAttributes({ calendarURL: studentGroupURLs[data] });
        break;
      case 'studentGroupFormField':
        newGroupQuery = handleFormField(newGroupQuery, data, studentGroups, multipleDepartments, 'group'); 
    }

    const updateStart = new Date();
    const updateStartStamp = updateStart.getTime();
    const updateEnd = new Date(new Date().setMonth(new Date().getMonth() + parseInt(newRange)));
    const updateEndStamp = updateEnd.getTime();
    const range = formatDate(updateStartStamp, updateEndStamp);
    const fetchURL = 'https://calendar.ua.edu/api/2/events?start=' + range[0] + '&end=' + range[1] + '&pp=50' + newGroupQuery + query + newMatch;

    fetch(fetchURL)
      .then(response => response.json())
      .then((events) => {
        if (events.events) {
          if (events.events.length) {
            setAttributes({ events: events.events });
          } else {
            setAttributes({ events: [] });
          }
        } else {
          setAttributes({ events: [] });
        }
      });
  }

  function formatDate(start, end) {
    let startDate = new Date(start);
    let endDate = new Date(end);
    startDate = startDate.getFullYear() + '-' + (startDate.getMonth() + 1) + '-' + startDate.getDate();
    endDate = endDate.getFullYear() + '-' + (endDate.getMonth() + 1) + '-' + endDate.getDate();
    return [startDate, endDate];
  }

  function handleFormField(newGroupQuery, data, activeGroup, inactiveGroup, key) {
    newGroupQuery = '';

    if (data) {
      let newData = [];

      if (inactiveGroup) {
        inactiveGroup.forEach((item) => {
          newGroupQuery += '&group_id[]=' + item;
        })
      }

      data.forEach((item, index) => {
        if (activeGroup && activeGroup.find(({ [key]: { name } }) => name == item)) {
          if (!newData.includes(activeGroup.find(({ [key]: { name } }) => name == item)[key].id.toString())) {
            newData.push(activeGroup.find(({ [key]: { name } }) => name == item)[key].id.toString());
            newGroupQuery += '&group_id[]=' + activeGroup.find(({ [key]: { name } }) => name == item)[key].id;
          }
        } else {
          newData.push(item);
          newGroupQuery += '&group_id[]=' + item;
        }
      });

      if (key === 'department') {
        setAttributes({ multipleDepartments: newData });
      } else if (key === 'group') {
        setAttributes({ multipleStudentGroups: newData });
      }

      setAttributes({ groupQuery: newGroupQuery });
    }

    return newGroupQuery;
  }

  function handleMultiple(newGroupQuery, data, activeGroup, inactiveGroup, updateType) {
    newGroupQuery = '';

    if (data) {
      if (inactiveGroup) {
        inactiveGroup.forEach((item) => {
          newGroupQuery += '&group_id[]=' + item;
        })
      }

      if (activeGroup) {
        if (!activeGroup.includes(data)) {
          activeGroup.push(data);
        }

        activeGroup.forEach((item) => {
          newGroupQuery += '&group_id[]=' + item;
        });
      } else {
        activeGroup = [];
        activeGroup.push(data);
        newGroupQuery += '&group_id[]=' + data;
      }

      setAttributes({ groupQuery: newGroupQuery });

      if(updateType === 'multipleDepartments') {
        setAttributes({ multipleDepartments: activeGroup });
      } else {
        setAttributes({ multipleStudentGroups: activeGroup });
      }
    }

    return newGroupQuery;
  }

  return (
    <div {...blockProps}>
      <BlockControls>
        <AlignmentToolbar
          value={textAlignment}
          onChange={(val) => setAttributes({ textAlignment: val })}
        />
      </BlockControls>

      <InspectorControls>
        <PanelBody title="Layout Settings">
          <__experimentalNumberControl
            label="Max Columns"
            isShiftStepEnabled={true}
            onChange={(currentMaxCount) => setAttributes({ maxColumns: parseInt(currentMaxCount) })}
            min={0}
            shiftStep={1}
            value={attributes.maxColumns}
          />

          <__experimentalNumberControl
            label="Number of Events"
            min={1}
            max={50}
            shiftStep={1}
            value={eventNum}
            onChange={value => setAttributes({ eventNum: parseInt(value) })}
          />
        </PanelBody>

        <PanelBody title="Feed Settings">
          <ToggleControl
            label="Match All"
            help='Untoggle this if you want to select multiple departments or student groups. Changes the filter from an AND to an OR relationship.'
            checked={matchAll}
            onChange={(value) => {
              updateFeed('matchAll', value);
            }}
          />
          {(matchAll) && (
            <>
              <RadioControl
                label="Filter By"
                selected={departmentOrGroup}
                options={[
                  { label: 'Department', value: 'department' },
                  { label: 'Student Group', value: 'group' },
                ]}
                onChange={(value) => {
                  updateFeed('departmentOrGroup', value);
                }}
              />

              {(departmentOrGroup == 'department' && departmentValues) && (
                <SelectControl
                  label="Department"
                  value={singleDepartment}
                  options={departmentValues.map(opt => ({ ...opt, label: decodeEntities(opt.label) }))}
                  onChange={(value) => {
                    updateFeed('singleDepartment', value);
                  }}
                />
              )}

              {(departmentOrGroup == 'group' && studentGroups) && (
                <SelectControl
                  label="Student Group"
                  value={singleGroup}
                  options={studentGroupValues.map(opt => ({ ...opt, label: decodeEntities(opt.label) }))}
                  onChange={(value) => {
                    updateFeed('singleGroup', value);
                  }}
                />
              )}
            </>
          )}

          {(!matchAll) && (
            <>
              {(departmentValues) && (
                <SelectControl
                  label="Department"
                  value={{ label: "Select a department", value: "" }}
                  options={departmentValues.map(opt => ({ ...opt, label: decodeEntities(opt.label) }))}
                  onChange={(value) => {
                    updateFeed('multipleDepartments', value);
                  }}
                />
              )}

              {(departmentValues) && (
                <FormTokenField
                  label=""
                  value={multipleDepartments}
                  suggestions={departmentNames.map(decodeEntities)}
                  onChange={(value) => {
                    updateFeed('departmentFormField', value);
                  }}
                  displayTransform={(token) => {
                    if (departments && departments.find(({ department: { id } }) => id == token)) {
                      return decodeEntities(departments.find(({ department: { id } }) => id == token).department.name);
                    }

                    return decodeEntities(token);
                  }}
                  __experimentalShowHowTo={false}
                />
              )}

              {(studentGroups) && (
                <SelectControl
                  label="Student Group"
                  value={{ label: "Select a group", value: "" }}
                  options={studentGroupValues.map(opt => ({ ...opt, label: decodeEntities(opt.label) }))}
                  onChange={(value) => {
                    updateFeed('multipleStudentGroups', value);
                  }}
                />
              )}

              {(studentGroups) && (
                <FormTokenField
                  label=""
                  value={multipleStudentGroups}
                  suggestions={studentGroupNames.map(decodeEntities)}
                  onChange={(value) => {
                    updateFeed('studentGroupFormField', value);
                  }}
                  displayTransform={(token) => {
                    if (studentGroups && studentGroups.find(({ group: { id } }) => id == token)) {
                      return decodeEntities(studentGroups.find(({ group: { id } }) => id == token).group.name);
                    }

                    return decodeEntities(token);
                  }}
                  __experimentalShowHowTo={false}
                />
              )}
            </>
          )}

          <SelectControl
            label="Date Range"
            value={dateRange}
            options={[
              { label: "1 Month", value: "1" },
              { label: "3 Months", value: "3" },
              { label: "6 Months", value: "6" },
              { label: "1 Year", value: "12" }
            ]}
            onChange={(value) => {
              setAttributes({ dateRange: value });
              updateFeed('range', value);
            }}
          />
        </PanelBody>

        <PanelBody title="Event Filters">
          <SelectControl
            label="Event Type"
            value={eventType}
            options={eventTypes.map(opt => ({ ...opt, label: decodeEntities(opt.label) }))}
            onChange={(value) => {
              setAttributes({ eventType: value });
              updateFeed('type', value, 'event_type');
            }}
          />

          <SelectControl
            label="Audience"
            value={audience}
            options={audiences.map(opt => ({ ...opt, label: decodeEntities(opt.label) }))}
            onChange={(value) => {
              setAttributes({ audience: value });
              updateFeed('type', value, 'audience');
            }}
          />

          <SelectControl
            label="Topic"
            value={topic}
            options={topics.map(opt => ({ ...opt, label: decodeEntities(opt.label) }))}
            onChange={(value) => {
              setAttributes({ topic: value });
              updateFeed('type', value, 'topic');
            }}
          />
        </PanelBody>
      </InspectorControls>

      <div className="event-feed ua_component_wrapper ua_layout--flow">
        <TextControl
          maxLength={50}
          onChange={(value) => setAttributes({ eventsHeading: value })}
          value={eventsHeading}
          className="page-header"
          placeholder="Upcoming Events"
        />

        <ul className="ua_layout--grid " style={inlineStyles} >
          {(events) && events.map(function (val, i) {
            if (i < eventNum) {
              const dateTime = new Date(val.event.event_instances[0].event_instance.start);
              const endDateTime = new Date(val.event.event_instances[0].event_instance.end);
              const date = dateTime.getDate();
              const month = dateTime.getMonth();
              const hours = dateTime.getHours();
              const endHours = endDateTime.getHours();
              const allDay = val.event.event_instances[0].event_instance.all_day;
              const experience = val.event.experience;

              let dateZeros = date;
              if (date < 10) {
                dateZeros = '0' + dateZeros;
              }

              let monthZeros = month;
              monthZeros = monthZeros + 1;
              if (monthZeros < 10) {
                monthZeros = '0' + monthZeros;
              }

              let minutes = dateTime.getMinutes();
              if (minutes === 0) {
                minutes = minutes + '0';
              }

              let endMinutes = endDateTime.getMinutes();
              if (endMinutes === 0) {
                endMinutes = endMinutes + '0';
              }

              let time = hours + ':' + minutes;
              let endTime = endHours + ':' + endMinutes;

              if (hours < 12) {
                time = time + ' a.m.';
              } else if (hours === 12) {
                time = time + ' p.m.';
              } else if (hours === 24) {
                time = (hours - 12) + ':' + minutes + ' a.m.';
              } else {
                time = (hours - 12) + ':' + minutes + ' p.m.';
              }

              if (endHours < 12) {
                endTime = endTime + ' a.m.';
              } else if (endHours === 12) {
                endTime = endTime + ' p.m.';
              } else if (endHours === 24) {
                endTime = (endHours - 12) + ':' + endMinutes + ' a.m.';
              } else {
                endTime = (endHours - 12) + ':' + endMinutes + ' p.m.';
              }

              if (time === '12:00 a.m.' || time === '0:00 a.m.') {
                time = '';
              }

              return (
                <li key={i}>
                  <div className="ua_component_wrapper">
                    <article className="ua_event">
                      <h3 className="ua_event_name">
                        <a href={val.event.localist_url}>{val.event.title}</a>
                      </h3>
                      <p className="ua_event_date">
                        <span className="ua_event_month">{fullMonthNames[month]} </span>
                        <span className="ua_event_date">{date}</span>
                      </p>
                      {(allDay) &&
                        <p className="ua_event_time">
                          <span className="fa fa-clock" title="Time" aria-hidden="true"></span>
                          <span className="ua_visually-hidden">Time</span>
                          {" "}All Day
                        </p>
                      }
                      {(time !== '') &&
                        <p className="ua_event_time">
                          <span className="fa fa-clock" title="Time" aria-hidden="true"></span>
                          <span className="ua_visually-hidden">Time</span>
                          {" " + time} - {endTime}
                        </p>
                      }
                      {(!(val.event.room_number === '' && val.event.location_name === '')) &&
                        <p className="ua_event_location">
                          <span className="fa fa-location-dot" title="Location" aria-hidden="true"></span>
                          <span className="ua_visually-hidden">Location</span>
                          {(experience === 'virtual') && ('Virtual Event')}
                          {" "}{(val.event.room_number) && (val.event.room_number + ', ')}
                          {val.event.location_name}
                        </p>
                      }
                    </article>
                  </div>
                </li>
              );
            }
          })}
        </ul>

        {(events && events.length === 0) && <p>There are no upcoming events in this category.</p>}

        {(calendarURL) &&
          <a className="ua_cta" href={calendarURL}>Calendar</a>
        }

        {(!calendarURL || calendarURL === '') &&
          <a className="ua_cta" href="https://calendar.ua.edu">Calendar</a>
        }
      </div>
    </div>
  );
}
