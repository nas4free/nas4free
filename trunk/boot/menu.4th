marker task-menu.4th

include /boot/screen.4th
include /boot/frames.4th

hide

variable menuidx
variable menubllt
variable menuX
variable menuY
variable promptwidth

variable bootkey
variable bootacpikey
variable bootsafekey
variable bootverbosekey
variable escapekey
variable rebootkey

46 constant dot

\ The logo. It can be 19 rows high and 34 columns wide.
: display-logo ( x y -- )
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
2dup at-xy ." " 1+
at-xy ." "
;

variable vmguest
: set-vmguest ( -- )
	0 vmguest !
	s" smbios.system.product" getenv dup -1 <> if
		2dup s" VMware Virtual Platform" compare 0= if
			1 vmguest !
		then
		2dup s" VirtualBox" compare 0= if
			1 vmguest !
		then
		2dup s" Virtual Machine" compare 0= if
			s" smbios.system.maker" getenv dup -1 <> if
				s" Microsoft Corporation" compare 0= if
					1 vmguest !
				then
			else
				drop
			then
		then
		2drop
	else
		drop
	then
	vmguest @ 0 <> if
		s" 1" s" nas4free.vmguest" setenv
		s" kern.hz" getenv dup -1 <> if
			?number if
				100 > if
					s" 100" s" kern.hz" setenv
				then
			then
		else
			drop
			s" 100" s" kern.hz" setenv
		then
	else
		s" 0" s" nas4free.vmguest" setenv
	then
;

: acpienabled? ( -- flag )
	s" acpi_load" getenv
	dup -1 = if
		drop false exit
	then
	s" YES" compare-insensitive 0<> if
		false exit
	then
	s" hint.acpi.0.disabled" getenv
	dup -1 <> if
		s" 0" compare 0<> if
			false exit
		then
	else
		drop
	then
	true
;

: printmenuitem ( -- n )
	menuidx @
	1+ dup
	menuidx !
	menuY @ + dup menuX @ swap at-xy
	menuidx @ .
	menuX @ 1+ swap at-xy
	menubllt @ emit
	menuidx @ 48 +
;

: display-menu ( -- )
	0 menuidx !
	dot menubllt !
	8 menuY !
	5 menuX !
	clear
	77 20 2 2 box
	45 3 display-logo
	5 7 at-xy ." Welcome to NAS4Free!"
	printmenuitem ."  Boot NAS4Free in Normal Mode" bootkey !
	s" arch-i386" environment? if
		drop
		printmenuitem ."  Boot NAS4Free with ACPI " bootacpikey !
		acpienabled? if
			." disabled"
		else
			." enabled"
		then
	else
		-2 bootacpikey !
	then
	printmenuitem ."  Boot NAS4Free in Safe Mode" bootsafekey !
	printmenuitem ."  Boot NAS4Free with verbose logging" bootverbosekey !
	printmenuitem ."  Escape to loader prompt" escapekey !
	printmenuitem ."  Reboot system" rebootkey !
	menuX @ 20 at-xy
	." Select option, [Enter] for default"
	menuX @ 21 at-xy
	s" or [Space] to pause timer    " dup 2 - promptwidth !
	type
;

: tkey
	seconds +
	begin 1 while
		over 0<> if
			dup seconds u< if
				drop
				-1
				exit
			then
			menuX @ promptwidth @ + 21 at-xy dup seconds - .
		then
		key? if
			drop
			key
			exit
		then
	50 ms
	repeat
;

set-current
set-vmguest

: menu-start
	s" menu_disable" getenv
	dup -1 <> if
		s" YES" compare-insensitive 0= if
			exit
		then
	else
		drop
	then
	display-menu
	s" autoboot_delay" getenv
	dup -1 = if
		drop
		10
	else
		0 0 2swap >number drop drop drop
	then
	begin
		dup tkey
		0 25 at-xy
		dup 32 = if nip 0 swap then
		dup -1 = if 0 boot then
		dup 13 = if 0 boot then
		dup 255 = if 0 boot then
		dup bootkey @ = if 0 boot then
		dup bootacpikey @ = if
			acpienabled? if
				s" acpi_load" unsetenv
				s" 1" s" hint.acpi.0.disabled" setenv
				s" 1" s" loader.acpi_disabled_by_user" setenv
			else
				s" YES" s" acpi_load" setenv
				s" 0" s" hint.acpi.0.disabled" setenv
			then
			0 boot
		then
		dup bootsafekey @ = if
			s" arch-i386" environment? if
				drop
				s" acpi_load" unsetenv
				s" 1" s" hint.acpi.0.disabled" setenv
				s" 1" s" loader.acpi_disabled_by_user" setenv
				s" 1" s" hint.apic.0.disabled" setenv
			then
			s" 0" s" hw.ata.ata_dma" setenv
			s" 0" s" hw.ata.atapi_dma" setenv
			s" 0" s" hw.ata.wc" setenv
			s" 0" s" hw.eisa_slots" setenv
			s" 1" s" hint.kbdmux.0.disabled" setenv
			s" 1" s" hint.est.0.disabled" setenv
			0 boot
		then
		dup bootverbosekey @ = if
			s" YES" s" boot_verbose" setenv
			0 boot
		then
		dup escapekey @ = if
			2drop
			s" NO" s" autoboot_delay" setenv
			exit
		then
		rebootkey @ = if 0 reboot then
	again
;

previous
