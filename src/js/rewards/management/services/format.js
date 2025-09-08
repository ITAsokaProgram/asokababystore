// services/format.js
export const fmt = {
  number(n) { return (n ?? 0).toLocaleString("id-ID"); },
  poin(n) { return `${(n ?? 0).toLocaleString("id-ID")} poin`; }
};

export const statusUI = {
  icon(status) {
    return ({ success:"fas fa-check", pending:"fas fa-clock", cancelled:"fas fa-times" }[status]) || "fas fa-question";
  },
  text(status) {
    return ({ success:"Berhasil", pending:"Proses", cancelled:"Dibatalkan" }[status]) || "Unknown";
  },
  cls(status) {
    return `status-${status}`;
  }
};
