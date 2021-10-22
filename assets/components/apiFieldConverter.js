// FIXME utiliser les paramètres uri… de patientParams
export default function (value, entity) {
    if (value === "") {
        return null;
    } else {
        return apiUri[entity].replace('%id%', value);
    }
}