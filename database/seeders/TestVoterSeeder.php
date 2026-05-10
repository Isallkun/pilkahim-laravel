<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Election;
use App\Models\Candidate;
use Illuminate\Database\Seeder;

class TestVoterSeeder extends Seeder
{
    public function run(): void
    {
        // Buat election aktif
        $election = Election::create([
            'name' => 'Pemilihan Ketua Umum Arutala 2026',
            'start_date' => now()->subHour(),
            'end_date' => now()->addHours(8),
            'status' => 'active',
            'result_visibility' => 'private',
        ]);

        // Buat 3 kandidat
        Candidate::create([
            'election_id' => $election->id,
            'name' => 'Ahmad Fauzi',
            'visi' => 'Mewujudkan HIMA Arutala yang inovatif, inklusif, dan berdaya saing tinggi.',
            'misi' => '1. Meningkatkan kualitas kegiatan kemahasiswaan. 2. Membangun jaringan alumni yang kuat. 3. Mendorong prestasi akademik dan non-akademik.',
            'sort_order' => 1,
        ]);

        Candidate::create([
            'election_id' => $election->id,
            'name' => 'Siti Nurhaliza',
            'visi' => 'HIMA Arutala sebagai wadah aspirasi dan pengembangan potensi mahasiswa.',
            'misi' => '1. Memperkuat solidaritas antar angkatan. 2. Mengoptimalkan program kerja berbasis kebutuhan mahasiswa. 3. Transparansi pengelolaan organisasi.',
            'sort_order' => 2,
        ]);

        Candidate::create([
            'election_id' => $election->id,
            'name' => 'Muhammad Rizki',
            'visi' => 'Bersama membangun HIMA Arutala yang progresif dan bermanfaat.',
            'misi' => '1. Digitalisasi sistem organisasi. 2. Kolaborasi dengan organisasi eksternal. 3. Peningkatan kesejahteraan mahasiswa.',
            'sort_order' => 3,
        ]);

        // Buat 10 pemilih untuk testing
        $voters = [
            ['username' => '001', 'name' => 'Abdulloh Salam', 'angkatan' => 'Panjer Pambayung', 'gender' => 'L'],
            ['username' => '002', 'name' => 'Achmad Syah Rafif', 'angkatan' => 'Panjer Pambayung', 'gender' => 'L'],
            ['username' => '003', 'name' => 'Adhim Yusuf', 'angkatan' => 'Panjer Pambayung', 'gender' => 'L'],
            ['username' => '004', 'name' => 'Ahmad Nur Badawi', 'angkatan' => 'Panjer Pambayung', 'gender' => 'L'],
            ['username' => '005', 'name' => 'Ayura Islakhah Zain', 'angkatan' => 'Panjer Pambayung', 'gender' => 'P'],
            ['username' => '006', 'name' => 'Desy Novelia Gatari', 'angkatan' => 'Bubuhan Danadyaksa', 'gender' => 'P'],
            ['username' => '007', 'name' => 'Devin Indra Kurniawan', 'angkatan' => 'Bubuhan Danadyaksa', 'gender' => 'L'],
            ['username' => '008', 'name' => 'Achmad Brilian Artianto', 'angkatan' => 'Arjuna Pangarsa', 'gender' => 'L'],
            ['username' => '009', 'name' => 'Almalika Suha Ramdania', 'angkatan' => 'Arjuna Pangarsa', 'gender' => 'P'],
            ['username' => '010', 'name' => 'Dina Kamalia Hidayati', 'angkatan' => 'Arjuna Pangarsa', 'gender' => 'P'],
        ];

        foreach ($voters as $data) {
            $voter = User::create([
                'username' => $data['username'],
                'name' => $data['name'],
                'password' => bcrypt('12345678'),
                'angkatan' => $data['angkatan'],
                'gender' => $data['gender'],
                'has_voted' => false,
                'password_changed_at' => now(), // Skip force password change untuk testing
            ]);

            $voter->assignRole('pemilih');
            $election->voters()->attach($voter->id, ['has_voted' => false, 'created_at' => now()]);
        }

        $this->command->info('10 test voters created (username 001-010, password "12345678")');
        $this->command->info('Election: ' . $election->name . ' (active)');
        $this->command->info('Candidates: 3');
    }
}
