import { FormControl, Select, CheckIcon, Radio } from 'native-base';
import React from 'react';
import { Platform } from 'react-native';
import { useQuery } from '@tanstack/react-query';
import { getVolumes } from '../../../util/api/item';
import { loadingSpinner } from '../../loadingSpinner';
import { loadError } from '../../loadError';
import _ from 'lodash';
import { getTermFromDictionary } from '../../../translations/TranslationService';

export const SelectVolume = (props) => {
     const { id, volume, setVolume, showModal, promptForHoldType, holdType, setHoldType, language, url } = props;

     const { status, data, error, isFetching } = useQuery({
          queryKey: ['volumes', id, url],
          queryFn: () => getVolumes(id, url),
          enabled: !!showModal,
     });

     if (!isFetching && data && _.isEmpty(volume)) {
          let volumesKeys = Object.keys(data);
          let key = volumesKeys[0];
          setVolume(data[key].volumeId);
     }

     return (
          <>
               {status === 'loading' || isFetching ? (
                    loadingSpinner()
               ) : status === 'error' ? (
                    loadError('Error', '')
               ) : (
                    <>
                         {promptForHoldType ? (
                              <FormControl>
                                   <Radio.Group
                                        name="holdTypeGroup"
                                        defaultValue={holdType}
                                        value={holdType}
                                        onChange={(nextValue) => {
                                             setHoldType(nextValue);
                                             setVolume('');
                                        }}
                                        accessibilityLabel="">
                                        <Radio value="item" my={1} size="sm">
                                             {getTermFromDictionary(language, 'first_available')}
                                        </Radio>
                                        <Radio value="volume" my={1} size="sm">
                                             {getTermFromDictionary(language, 'specific_volume')}
                                        </Radio>
                                   </Radio.Group>
                              </FormControl>
                         ) : null}
                         {holdType === 'volume' ? (
                              <FormControl>
                                   <FormControl.Label>{getTermFromDictionary(language, 'select_volume')}</FormControl.Label>
                                   <Select
                                        isReadOnly={Platform.OS === 'android'}
                                        name="volumeForHold"
                                        selectedValue={volume}
                                        defaultValue={volume}
                                        minWidth="200"
                                        accessibilityLabel={getTermFromDictionary(language, 'select_volume')}
                                        _selectedItem={{
                                             bg: 'tertiary.300',
                                             endIcon: <CheckIcon size="5" />,
                                        }}
                                        mt={1}
                                        mb={2}
                                        onValueChange={(itemValue) => setVolume(itemValue)}>
                                        {_.map(data, function (item, index, array) {
                                             return <Select.Item label={item.label} value={item.volumeId} key={index} />;
                                        })}
                                   </Select>
                              </FormControl>
                         ) : null}
                    </>
               )}
          </>
     );
};