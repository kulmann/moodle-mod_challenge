import replace from "lodash/replace";
import forEach from "lodash/forEach";

export default {
  filters: {
    stringParams: stringParamsInternal,
  },
  methods: {
    stringParams: stringParamsInternal,
  },
};

function stringParamsInternal(translation, params) {
  let tmp = translation;
  if (translation.includes("{$a}")) {
    return replace(tmp, "{$a}", params);
  } else if (translation.includes("{$a->")) {
    forEach(params, function (value, key) {
      tmp = replace(tmp, "{$a->" + key + "}", value);
    });
    return tmp;
  }
}
