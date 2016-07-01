conn = new Mongo();
//ttdb = conn.getDB('trucker_tracker');
admindb = conn.getDB('admin');
admindb.createUser(
    {
        user: "root",
        pwd: "64cXeTETiB",
        roles: [ { role: "root", db: "admin" } , { role: "readWriteAnyDatabase", db: "admin" }]
    }
);
// admindb.createUser(
//     {
//         user:"trucker_tracker",
//         pwd:"6iSgcH2eNE",
//         roles: [ { role: "dbOwner", db: "trucker_tracker" }, { role: "readWrite", db: "trucker_tracker" } ]
//     }
// ); http://81dc625ebdea69785a0a216cca731b34:60d16c02c46d839bddcbf58db0ac5dd8@mcsweeneytg.com.au:8000/incoming/message
