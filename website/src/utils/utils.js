/**
 * Return a new object with the values at each key mapped using fn(key, value).
 * 
 * @param {Object} obj The object to map.
 * @param {Function} fn The mapping function.
 * @param {boolean} reassociate If given and false, instead of returning a new
 *   object, return a new array containing only the mapped values (the
 *   keys (property names) of the given object are lost).
 */
export function mapObj(obj, fn, reassociate = true) {
    let i = 0;
    const ret = reassociate ? {} : [];
    for (const key in obj) {
        const val = fn(key, obj[key], i);
        if (reassociate) {
            ret[key] = val;
        } else {
            ret.push(val);
        }

        i++;
    }
    return ret;
}

/**
 * Return a new object with the values at each key filtered using
 * fn(key, value).
 * 
 * @param {Object} obj The object to filter.
 * @param {Function} fn The filtering function.
 * @param {boolean} reassociate If given and false, instead of returning a new
 *   object, return a new array containing only the mapped values (the
 *   keys (property names) of the given object are lost).
 */
 export function filterObj(obj, fn, reassociate = true) {
    let i = 0;
    const ret = reassociate ? {} : [];
    for (const key in obj) {
        if (fn(key, obj[key], i)) {
            if (reassociate) {
                ret[key] = obj[key];
            } else {
                ret.push(obj[key]);
            }
        }

        i++;
    }
    return ret;
}

/**
 * Returns the first non-null value in the list.
 *
 * @param {Array<any>} objs A list of objects that each may be null.
 * @param {any} defaultValue The value to return if all values in the list are
 *   null.
 * @returns {any} The first non-undefined, non-null object in the list. Defautls
 *   to defaultValue (null by default) if only nulls are given as objs.
 */
export function firstDefined(objs, defaultValue = null) {
    for (const obj of objs) {
        if (obj != null) return obj;
    }
    return defaultValue;
};

/**
 * Return a new object with all entries with null (and undefined) values
 * removed.
 * 
 * @param {object} obj The object to filter.
 * @returns {object} The new object.
 */
export function optionalEntries(obj) {
    return Object.fromEntries(
        Object.entries(obj).filter(([key, value]) => value != null)
    );
}

/**
 * Join the given list of strings with the given separator.
 * 
 * @param {string} separator The string to insert between each non-null element
 *   of the given strings.
 * @param {Array<string>} strings The list of strings to join.
 * @returns {string} A string containing each non-null string given placed next
 *   to each other in order, separated by the given separator.
 */
export function optionalJoin(separator, strings) {
    return strings.filter((val) => val != null).join(separator);
}

const utils = Object.freeze({
    mapObj,
    firstDefined,
    optionalEntries,
    optionalJoin
});
export default utils;
