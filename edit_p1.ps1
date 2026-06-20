$file = "app/Http/Controllers/SuperAdmin/AkreditasiController.php"
$lines = Get-Content $file

# Insert PesantrenService after BandingService
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match "use App\\\\Services\\\\BandingService;") {
        $lines = @($lines[0..$i]) + @("use App\Services\PesantrenService;") + @($lines[($i+1)..($lines.Count-1)])
        break
    }
}

# Add PesantrenService to constructor param list
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match "private AssessorWorkloadService") {
        $lines[$i] = $lines[$i] -replace "\)$", ","
        $lines = @($lines[0..$i]) + @("        private PesantrenService `$pesantrenService,") + @("    ) {}")
        $i++
        continue
    }
    if ($lines[$i] -match "^    \) \{\}$") {
        # remove old closing
        $lines = @($lines[0..($i-1)]) + @($lines[($i+1)..($lines.Count-1)])
        break
    }
}

Set-Content $file ($lines -join "`r`n")
Write-Output "Phase 1 done"
