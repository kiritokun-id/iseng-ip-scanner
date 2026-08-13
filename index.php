<?php
include 'db.php';
$menu = isset($_GET['menu']) ? $_GET['menu'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPAM Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #090d16;
            color: #f8fafc;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }
    </style>
</head>
<body class="bg-[#090d16] text-slate-100 min-h-screen flex flex-col">

    <header class="w-full bg-[#111827] border-b border-slate-800/80 px-6 py-4 flex items-center justify-between sticky top-0 z-50 shadow-md">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-slate-800/90 text-cyan-400 rounded-lg border border-slate-700">
                <i class="fa-solid fa-network-wired text-lg"></i>
            </div>
            <h1 class="text-xl font-bold tracking-wide text-white">IPAM Dashboard</h1>
        </div>

        <nav class="hidden md:flex items-center gap-2">
            <a href="index.php?menu=dashboard" class="px-4 py-2 rounded-lg text-sm font-medium transition duration-200 <?= $menu=='dashboard' ? 'bg-cyan-500 text-slate-950 font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-cyan-400' ?>">
                Dashboard
            </a>
            <a href="index.php?menu=scanner" class="px-4 py-2 rounded-lg text-sm font-medium transition duration-200 <?= $menu=='scanner' ? 'bg-cyan-500 text-slate-950 font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-cyan-400' ?>">
                Live Scanner
            </a>
            <a href="index.php?menu=subnet" class="px-4 py-2 rounded-lg text-sm font-medium transition duration-200 <?= $menu=='subnet' ? 'bg-cyan-500 text-slate-950 font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-cyan-400' ?>">
                Block Subnet
            </a>
            <a href="index.php?menu=organization" class="px-4 py-2 rounded-lg text-sm font-medium transition duration-200 <?= $menu=='organization' ? 'bg-cyan-500 text-slate-950 font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-cyan-400' ?>">
                Organisasi
            </a>
        </nav>

        <div class="md:hidden flex items-center">
            <button id="mobileMenuBtn" class="text-slate-300 hover:text-cyan-400 focus:outline-none p-2">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </div>
    </header>

    <div id="mobileMenu" class="hidden md:hidden bg-[#111827] border-b border-slate-800 px-6 py-4 space-y-2">
        <a href="index.php?menu=dashboard" class="block px-3 py-2 rounded-lg text-sm font-medium <?= $menu=='dashboard' ? 'bg-cyan-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-cyan-400' ?>">Dashboard</a>
        <a href="index.php?menu=scanner" class="block px-3 py-2 rounded-lg text-sm font-medium <?= $menu=='scanner' ? 'bg-cyan-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-cyan-400' ?>">Live Scanner</a>
        <a href="index.php?menu=subnet" class="block px-3 py-2 rounded-lg text-sm font-medium <?= $menu=='subnet' ? 'bg-cyan-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-cyan-400' ?>">Block Subnet</a>
        <a href="index.php?menu=organization" class="block px-3 py-2 rounded-lg text-sm font-medium <?= $menu=='organization' ? 'bg-cyan-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-cyan-400' ?>">Organisasi</a>
    </div>

    <main class="flex-1 max-w-7xl w-full mx-auto px-6 py-8">
        <?php
        if ($menu == 'dashboard') include 'dashboard.php';
        elseif ($menu == 'organization') include 'organization.php';
        elseif ($menu == 'subnet') include 'subnet.php';
        elseif ($menu == 'scanner') include 'scanner.php';
        else include 'dashboard.php';
        ?>
    </main>

    <script>
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
