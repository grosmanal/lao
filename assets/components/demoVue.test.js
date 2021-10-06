import WeekAvailability from './availability/weekAvailability';
import { mount } from '@vue/test-utils';

test('mount a vue component', async () => {
    let wrapper = mount(WeekAvailability, {
        propsData: {
            patientId: 1,
            middleOfDay: "1300",
            initAvailability: JSON.stringify({
                "1": {
                    "0800-0830": false,
                    "0830-0930": true
                }
            })
        },
        data() {
            return {
                availability: {
                "1": {
                    "0800-0830": false,
                    "0830-0930": true
                }
            }
            };
        }
    });
    
    let button = wrapper.find('.slot-available button');
    expect(button.exists()).toBe(true);
    console.log(wrapper.find('.slot-available button').html());
    await button.trigger('click').then(function () {
        console.log(wrapper.html());
    });
    
    //expect(wrapper.html()).toMatchSnapshot();
}) 