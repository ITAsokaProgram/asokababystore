export const fetchTransaksi = async (token, hp, limit = null) => {
  const requestBody = { kode: hp };

  if (limit) {
    requestBody.limit = limit;
  }

  const response = await fetch(`/src/api/customer/history_transaction`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify(requestBody),
  });

  if (response.status === 401) {
    window.location.href = "/src/component/error_token";
  } else {
    const data = await response.json();
    return data;
  }
};

export default fetchTransaksi;
