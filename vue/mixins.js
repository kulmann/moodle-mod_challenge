import _ from 'lodash';

export default {
    filters: {
        stringParams: stringParamsInternal,
    },
    methods: {
        stringParams: stringParamsInternal,
    }
}

function stringParamsInternal(translation, params) {
    let tmp = translation;
    if (translation.includes('{$a}')) {
        return _.replace(tmp, '{$a}', params);
    } else if (translation.includes('{$a->')) {
        _.forEach(params, function (value, key) {
            tmp = _.replace(tmp, '{$a->' + key + '}', value);
        });
        return tmp;
    }
}
