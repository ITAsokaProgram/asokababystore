<form method="POST" id="data_cust">
        <div id="modalMemberProfile"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div id="modalContentProfile"
                class="transition duration-300 ease-out bg-white w-11/12 max-w-2xl p-6 rounded-xl shadow-xl opacity-0 scale-90 overflow-y-auto max-h-[90vh]">

                <h2 class="text-2xl font-bold mb-4 text-pink-600">Update Profile Member</h2>
                <p class="text-gray-600">Silahkan isi data diri Anda dengan lengkap.</p>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md text-sm mb-4">
                    <p class="font-semibold">📝 Catatan :</p>
                    <p>Pilih provinsi terlebih dahulu agar data wilayah lain tampil, pilih secara berurutan ya terimakasih 🙏</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label for="member" class="block text-sm font-semibold mb-1">Kode Member / No HP</label>
                        <input type="text" required id="memberKode" name="kode_member" pattern="[0-9]" maxlength="13"
                            placeholder="Contoh: 08123456789" readonly class="w-full border p-2 rounded" />
                    </div>
                    <div class="col-span-2">
                        <label for="nama_lengkap" class="block text-sm font-semibold mb-1">Nama Lengkap</label>
                        <input type="text" required id="nama_lengkap" name="nama_lengkap" pattern="[a-zA-Z\s]+"
                            maxlength="50" placeholder="Nama sesuai KTP" class="w-full border p-2 rounded" />
                    </div>
                    <div class="col-span-2">
                        <label for="alamat_ktp" class="block text-sm font-semibold mb-1">Alamat KTP</label>
                        <input type="text" required id="alamat_ktp" name="alamat_ktp" class="w-full border p-2 rounded"
                            placeholder="Alamat sesuai ktp" />
                    </div>
                    <div class="col-span-2">
                        <label for="provinsi" class="block text-sm font-semibold mb-1">Provinsi</label>
                        <select required id="provinsi" name="provinsi" class="w-full border p-2 rounded">
                            <option value="">Pilih Provinsi</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label for="kota" class="block text-sm font-semibold mb-1">Kota / Kabupaten</label>
                        <select required id="kota" name="kota" class="w-full border p-2 rounded">
                            <option value="">Pilih Kota/Kab</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label for="kec" class="block text-sm font-semibold mb-1">Kecamatan</label>
                        <select required id="kec" name="kec" class="w-full border p-2 rounded">
                            <option value="">Pilih Kecamatan</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label for="kel" class="block text-sm font-semibold mb-1">Kelurahan</label>
                        <select required id="kel" name="kel" class="w-full border p-2 rounded">
                            <option value="">Pilih Kelurahan</option>
                        </select>
                    </div>
                    <div id="checkbox-container" class="flex items-center gap-2 col-span-2 mt-2">
                        <input type="checkbox" id="sesuai" />
                        <label for="sesuai" class="text-sm text-black">Alamat Domisili sama dengan KTP</label>
                    </div>
                    <div class="col-span-2">
                        <label for="alamat_domisili" class="block text-sm font-semibold mb-1">Alamat Domisili</label>
                        <input type="text" name="alamat_domisili" required placeholder="Alamat Domisili"
                            id="alamat_domisili" class="w-full border p-2 rounded" />
                    </div>
                    <div class="col-span-2">
                        <label for="provinsi_domisili" class="block text-sm font-semibold mb-1">Provinsi
                            Domisili</label>
                        <select type="text" name="provinsi_domisili" required id="provinsi_domisili"
                            placeholder="Domisili Provinsi" class="w-full border p-2 rounded">
                            <option value="">Provinsi Domisili</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label for="kota_domisili" class="block text-sm font-semibold mb-1">Kab/Kota Domisili</label>
                        <select type="text" required id="kota_domisili" name="kota_domisili"
                            placeholder="Domisili Kab/Kota" class="w-full border p-2 rounded">
                            <option value="">Kota Domisili</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label for="kecamatan_domisili" class="block text-sm font-semibold mb-1">Kecamatan
                            Domisili</label>
                        <select type="text" required id="kecamatan_domisili" name="kec_domisili"
                            placeholder="Domisili Kecamatan" class="w-full border p-2 rounded">
                            <option value="">Kecamatan Domisili</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label for="kelurahan_domisili" class="block text-sm font-semibold mb-1">Kelurahan
                            Domisili</label>
                        <select type="text" required id="kelurahan_domisili" name="kel_domisili"
                            placeholder="Domisili Kelurahan" class="w-full border p-2 rounded">
                            <option value="">Kelurahan Domisili</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label for="no_nik" class="block text-sm font-semibold mb-1">Nomor NIK</label>
                        <input type="text" required id="no_nik" name="nik" pattern="[0-9]+" placeholder="NIK KTP" onchange="validateNIK()"
                            maxlength="16" class="w-full border p-2 rounded" />
                        <p id="nik-error" class="mt-1 text-red-600 text-sm hidden">NIK harus 16 angka</p>
                    </div>
                    <div class="col-span-2">
                        <label for="no_hp" class="block text-sm font-semibold mb-1">No HP</label>
                        <input type="text" required id="no_hp" name="no_hp" pattern="[0-9]+" placeholder="NO HP Aktif"
                            maxlength="13" class="w-full border p-2 rounded" />
                    </div>
                    <div class="col-span-2">
                        <label for="member-email" class="block text-sm font-semibold mb-1">Email</label>
                        <input type="email" required id="member-email" name="email" placeholder="Email Aktif" autocomplete="on"
                            class="w-full border p-2 rounded" />
                    </div>
                    <div class="col-span-2">
                        <label for="tanggal_lahir" class="block text-sm font-semibold mb-1">Tanggal Lahir</label>
                        <input type="date" required id="tanggal_lahir" name="tanggal_lahir"
                            class="w-full border p-2 rounded" />
                    </div>
                    <div class="col-span-2">
                        <label for="jenis_kelamin" class="block text-sm font-semibold mb-1">Jenis Kelamin</label>
                        <select id="jenis_kelamin" name="jenis_kelamin" class="w-full border p-2 rounded">
                            <option value="Laki-Laki">Laki-Laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label for="jumlah_anak" class="block text-sm font-semibold mb-1">Jumlah Anak</label>
                        <select id="jumlah_anak" name="jumlah_anak" class="w-full border p-2 rounded">
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 col-span-2 mt-2">
                        <input type="checkbox" required id="syarat" />
                        <label for="syarat" class="text-sm text-black">Syarat Dan Ketentuan</label>
                    </div>
                </div>

                <!-- Tombol aksi -->
                <div class="flex justify-end space-x-2">
                    <button id="closeModalProfile"
                        class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 transition mb-3">Tutup</button>
                    <button type="submit" id="send_data"
                        class="px-4 py-2 rounded bg-pink-600 text-white hover:bg-pink-700 transition mb-3">Submit</button>
                </div>
            </div>
        </div>
    </form>
    <script src="/../../src/js/send_info_cust.js"></script>