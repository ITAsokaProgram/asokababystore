// services/dates.js
// Standardisasi tanggal: untuk hitung "hari ini"
export function todayID() {
  const d = new Date();
  return d.toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric" }); // "15 Des 2024"
}

// Helper buat transaksi baru
export function nowParts() {
  const now = new Date();
  return {
    tanggal: now.toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric" }),
    jam: now.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" })
  };
}
