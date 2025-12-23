// services/format.js
export const fmt = {
  number(n) { return (n ?? 0).toLocaleString("id-ID"); },
  poin(n) { return `${(n ?? 0).toLocaleString("id-ID")} poin`; }
};

export const statusUI = {
  icon(status) {
    return ({ claimed:"fas fa-check", pending:"fas fa-clock", expired:"fas fa-times" }[status]) || "fas fa-question";
  },
  text(status) {
    return ({ claimed:"Berhasil", pending:"Proses", expired:"Dibatalkan" }[status]) || "Unknown";
  },
  cls(status) {
    return `status-${status}`;
  }
};
