//This is info from mysql database that would be fetched from the KEY_COLUMN_USAGE table, here inserted manually, to replace fields/fields2...
var fields_1 = [
    { "Field": "id", "Type": "int(11)", "Null": "NO", "Key": "PRI", "Default": null, "Extra": "auto_increment" },
    { "Field": "c_short_name", "Type": "varchar(11)", "Null": "YES", "Key": "", "Default": null, "Extra": "" },
    { "Field": "location", "Type": "char(2)", "Null": "NO", "Key": "", "Default": null, "Extra": "" },
    { "Field": "insert_date", "Type": "datetime", "Null": "NO", "Key": "", "Default": "current_timestamp()", "Extra": "" }
];

var fields_2 = [
    { "Field": "id", "Type": "int(11)", "Null": "NO", "Key": "PRI", "Default": null, "Extra": "auto_increment" },
    { "Field": "f_short_name", "Type": "varchar(11)", "Null": "YES", "Key": "", "Default": null, "Extra": "" },
    { "Field": "insert_date", "Type": "datetime", "Null": "NO", "Key": "", "Default": "current_timestamp()", "Extra": "" },
    { "Field": "imdb_code", "Type": "varchar(55)", "Null": "YES", "Key": "", "Default": null, "Extra": "" }
];

var fields_3 = [
    { "Field": "id", "Type": "int(11)", "Null": "NO", "Key": "PRI", "Default": null, "Extra": "auto_increment" },
    { "Field": "name", "Type": "varchar(80)", "Null": "NO", "Key": "", "Default": null, "Extra": "" },
    { "Field": "film_id", "Type": "int(11)", "Null": "NO", "Key": "MUL", "Default": null, "Extra": "", "Referenced_table": "film", "Referenced_field": "id" },
    { "Field": "insert_date", "Type": "datetime", "Null": "NO", "Key": "", "Default": "current_timestamp()", "Extra": "" }
]; //date: 2022-02-03