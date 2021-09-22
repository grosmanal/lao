export default function (value, entity) {
    if (value === "") {
        return null;
    } else {
        return '/api/' + entity + '/' + value;
    }
}