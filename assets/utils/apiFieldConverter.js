// apiUri est défini dans base.html.twig
export default function (value, entity) {
    if (value === "") {
        return null;
    } else {
        return apiUri[entity].replace('%id%', value);
    }
}