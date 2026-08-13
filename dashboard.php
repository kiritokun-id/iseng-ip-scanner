<?php
$q_org = $conn->query("SELECT COUNT(*) as total FROM organizations")->fetch_assoc()['total'];
$q_asn = $conn->query("SELECT COUNT(*) as total FROM as_numbers")->fetch_assoc()['total'];
$q_sub = $conn->query("SELECT COUNT(*) as total FROM block_subnets")->fetch_assoc()['total'];
$q_ip  = $conn->query("SELECT COUNT(*) as total FROM ip_addresses WHERE status='Active'")->fetch_assoc()['total'];
$q_total_ip = $conn->query("SELECT COUNT(*) as total FROM ip_addresses")->fetch_assoc()['total'];

?>

<div class="space-y-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-[#111827] border border-slate-800 rounded-xl p-6 flex items-center justify-between shadow-lg hover:border-cyan-500/40 transition duration-300">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Total Instansi</p>
                <h3 class="text-3xl font-bold text-white mt-1"><?= $q_org ?></h3>
            </div>
            <div class="p-3.5 bg-slate-800/90 text-cyan-400 rounded-xl border border-slate-700/60 shadow-sm">
                <i class="fa-solid fa-building text-xl"></i>
            </div>
        </div>

        <div class="bg-[#111827] border border-slate-800 rounded-xl p-6 flex items-center justify-between shadow-lg hover:border-cyan-500/40 transition duration-300">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Total AS Number</p>
                <h3 class="text-3xl font-bold text-white mt-1"><?= $q_asn ?></h3>
            </div>
            <div class="p-3.5 bg-slate-800/90 text-cyan-400 rounded-xl border border-slate-700/60 shadow-sm">
                <i class="fa-solid fa-network-wired text-xl"></i>
            </div>
        </div>

        <div class="bg-[#111827] border border-slate-800 rounded-xl p-6 flex items-center justify-between shadow-lg hover:border-cyan-500/40 transition duration-300">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Blok Subnet</p>
                <h3 class="text-3xl font-bold text-white mt-1"><?= $q_sub ?></h3>
            </div>
            <div class="p-3.5 bg-slate-800/90 text-cyan-400 rounded-xl border border-slate-700/60 shadow-sm">
                <i class="fa-solid fa-server text-xl"></i>
            </div>
        </div>

        <div class="bg-[#111827] border border-slate-800 rounded-xl p-6 flex items-center justify-between shadow-lg hover:border-cyan-500/40 transition duration-300">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">IP Aktif Terdeteksi</p>
                <h3 class="text-3xl font-bold text-cyan-400 mt-1"><?= $q_ip ?> <span class="text-slate-500 text-lg font-normal">/ <?= $q_total_ip ?></span></h3>
            </div>
            <div class="p-3.5 bg-slate-800/90 text-cyan-400 rounded-xl border border-slate-700/60 shadow-sm">
                <i class="fa-solid fa-desktop text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-[#111827] border border-slate-800 rounded-2xl p-6 lg:p-8 shadow-xl">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-800">
            <i class="fa-solid fa-compass text-cyan-400 text-xl"></i>
            <h4 class="text-lg font-bold text-white tracking-wide">Pintasan Cepat Sistem IPAM</h4>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-[#090d16] border border-slate-800 rounded-xl p-6 flex flex-col justify-between hover:border-cyan-500/50 transition duration-300 shadow-md">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-3 bg-slate-800 text-cyan-400 rounded-lg border border-slate-700">
                            <i class="fa-solid fa-play me-2"></i>
                        </div>
                        <h5 class="text-base font-bold text-white">Live Scanner IP</h5>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed mb-6">
                        Lakukan pemindaian subnet network secara real-time untuk mendeteksi perangkat aktif.
                    </p>
                </div>
                <div>
                    <a href="index.php?menu=scanner" class="inline-flex items-center justify-center w-full py-2.5 px-4 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-semibold rounded-lg transition duration-200 text-sm shadow-sm">
                        <i class="fa-solid fa-play me-2"></i> Buka Scanner
                    </a>
                </div>
            </div>

            <div class="bg-[#090d16] border border-slate-800 rounded-xl p-6 flex flex-col justify-between hover:border-cyan-500/50 transition duration-300 shadow-md">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-3 bg-slate-800 text-cyan-400 rounded-lg border border-slate-700">
                            <i class="fa-solid fa-network-wired text-lg"></i>
                        </div>
                        <h5 class="text-base font-bold text-white">Block Subnet</h5>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed mb-6">
                        Kelola blok subnet, alokasi IP, serta relasi instansi dan AS number.
                    </p>
                </div>
                <div>
                    <a href="index.php?menu=subnet" class="inline-flex items-center justify-center w-full py-2.5 px-4 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-semibold rounded-lg transition duration-200 text-sm shadow-sm">
                        <i class="fa-solid fa-folder-open me-2"></i> Kelola Subnet
                    </a>
                </div>
            </div>

            <div class="bg-[#090d16] border border-slate-800 rounded-xl p-6 flex flex-col justify-between hover:border-cyan-500/50 transition duration-300 shadow-md">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-3 bg-slate-800 text-cyan-400 rounded-lg border border-slate-700">
                            <i class="fa-solid fa-building text-lg"></i>
                        </div>
                        <h5 class="text-base font-bold text-white">Organisasi</h5>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed mb-6">
                        Kelola daftar instansi dan manajemen AS Number terikat.
                    </p>
                </div>
                <div>
                    <a href="index.php?menu=organization" class="inline-flex items-center justify-center w-full py-2.5 px-4 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-semibold rounded-lg transition duration-200 text-sm shadow-sm">
                        <i class="fa-solid fa-sitemap me-2"></i> Kelola Organisasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
