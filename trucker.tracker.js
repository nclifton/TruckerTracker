conn = new Mongo();
ttdb = conn.getDB('trucker_tracker');
admindb = conn.getDB('admin');
admindb.createUser(
    {
        user: "root",
        pwd: "64cXeTETiB",
        roles: [ { role: "root", db: "admin" } , { role: "readWriteAnyDatabase", db: "admin" }]
    }
)
admindb.createUser(
    {
        user:"trucker_tracker",
        pwd:"6iSgcH2eNE",
        roles: [ { role: "dbOwner", db: "trucker_tracker" }, { role: "readWrite", db: "trucker_tracker" } ]
    }
);
