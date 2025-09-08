import getCookie from "../../utils/cookies.js";
const dataHadiah = async () => {
    try {
        const token = getCookie("token");
        const response = await fetch("/src/api/customer/get_hadiah_pubs", {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${token}`,
            },
        });
        const data = await response.json();

        if (response.status === 200) {
            return data.data;
        } else {
            console.log(data.message);
        }
    } catch (error) {
        console.log(error);
    }
}

const dataPoin = async () => {
    try {
        const token = getCookie("token");
        const response = await fetch("/src/api/customer/get_poin_customer", {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${token}`,
            },
        });
        const data = await response.json();

        if (response.status === 200) {
            return data.data;
        } else {
            console.log(data.message);
        }
    } catch (error) {
        console.log(error);
    }
}

const historyTukar = async (page = 1, limit = 10) => {
    try {
        const token = getCookie("token");
        const url = `/src/api/poin/get_hadiah_tukar?page=${page}&limit=${limit}`;
        const response = await fetch(url, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${token}`,
            },
        });
        const data = await response.json();

        if (response.status === 200) {
            return {
                data: data.data,
                pagination: data.pagination
            };
        } else {
            console.log(data.message);
            return { data: [], pagination: null };
        }
    } catch (error) {
        console.log(error);
        return { data: [], pagination: null };
    }
}

export { dataHadiah, dataPoin, historyTukar };