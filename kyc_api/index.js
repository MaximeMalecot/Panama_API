require("dotenv").config();

const express = require("express");
const app = express();
const fetch = (...args) => import('node-fetch').then(({default: fetch}) => fetch(...args));
app.use(express.json());

app.post("/check", async (req, res) => {
    try {
        console.log(req.body)
        if (!req.body) throw new Error("Missing body");
        if (!req.body.data) throw new Error("Missing data");
        if (!req.body.uri) throw new Error("Missing uri ");

        const { siret, name, surname } = req.body.data;
        const { success_uri, error_uri } = req.body.uri;

        if (!siret || !name || !surname)
            throw new Error("Missing data field(s)");
        if (!success_uri || !error_uri)
            throw new Error("Missing one or both uri");
        
        console.log("valid request");

        res.sendStatus(204);

        setTimeout(async () => {
            console.log("sending success");
            await fetch(success_uri, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `${process.env.API_SECRET}`,
                    Host: `${process.env.API_HOST}`,
                },
            });
            console.log("Request finished");
        }, 3000);

    } catch (e) {
        console.log(e);
        res.sendStatus(400);
        return;
    }
});

app.listen(3000, () => {
    console.log("KYC Server started on port 3000");
});
