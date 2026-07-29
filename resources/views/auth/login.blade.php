<x-layout.app title="VolunteerHub - Secure Login">
    <div class="flex-grow flex items-center justify-center p-4 md:p-12 min-h-screen bg-gradient-to-tr from-slate-900 via-jci-dark to-jci-blue">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-5xl w-full grid grid-cols-1 md:grid-cols-12 min-h-[600px]">
            
            <!-- Left Banner: Information, Mission, & Aesthetics -->
            <div class="md:col-span-5 bg-gradient-to-b from-jci-blue to-jci-dark p-8 md:p-12 text-white flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -top-12 -left-12 w-48 h-48 bg-white/5 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-20 -right-20 w-72 h-72 bg-jci-accent/10 rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    <span class="bg-jci-accent text-jci-dark text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider">Surigao City, Caraga</span>
                    <h2 class="text-3xl font-extrabold mt-4 tracking-tight leading-tight">Empowering leaders, impacting lives.</h2>
                    <p class="text-sm text-slate-300 mt-3 leading-relaxed">Join the JCI Surigao Wensies digital volunteer platform to plan events, track certifications, and connect with local communities.</p>
                </div>

                <div class="mt-8 relative z-10 border-t border-white/10 pt-6">
                    <p class="text-xs text-slate-400 italic">"Socio-civic leadership starts with simple actions. Coordinate, volunteer, and make a persistent difference."</p>
                    <div class="flex items-center gap-3 mt-4">
                        <i class="fa-solid fa-hands-holding-child text-jci-accent text-xl"></i>
                        <div>
                            <h5 class="text-xs font-bold text-white">JCI Surigao Wensies Chapter</h5>
                            <p class="text-[10px] text-slate-400">Official VolunteerHub Portal</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Form: Actual Role Selection & Access Credentials -->
            <div class="md:col-span-7 p-8 md:p-12 flex flex-col justify-center">
                <div class="mb-6">
                    <h3 class="text-2xl font-black text-slate-900">Welcome to VolunteerHub</h3>
                    <p class="text-sm text-slate-500 mt-1">Please select your account type and enter credentials to continue.</p>
                </div>

                <!-- Role Selector -->
                <div class="grid grid-cols-3 gap-2 mb-6">
                    <button type="button" onclick="selectAuthRole('admin')" id="authRole-admin" class="auth-role-btn border-2 border-jci-blue bg-blue-50/50 text-jci-blue rounded-xl p-3 flex flex-col items-center justify-center text-center transition duration-200 hover:bg-blue-50">
                        <i class="fa-solid fa-user-shield text-lg mb-1"></i>
                        <span class="text-[11px] font-bold">Admin</span>
                    </button>
                    <button type="button" onclick="selectAuthRole('org')" id="authRole-org" class="auth-role-btn border border-slate-200 text-slate-600 rounded-xl p-3 flex flex-col items-center justify-center text-center transition duration-200 hover:border-jci-blue hover:text-jci-blue hover:bg-blue-50/20">
                        <i class="fa-solid fa-building-ngo text-lg mb-1"></i>
                        <span class="text-[11px] font-bold">Organization</span>
                    </button>
                    <button type="button" onclick="selectAuthRole('volunteer')" id="authRole-volunteer" class="auth-role-btn border border-slate-200 text-slate-600 rounded-xl p-3 flex flex-col items-center justify-center text-center transition duration-200 hover:border-jci-blue hover:text-jci-blue hover:bg-blue-50/20">
                        <i class="fa-solid fa-handshake-angle text-lg mb-1"></i>
                        <span class="text-[11px] font-bold">Volunteer</span>
                    </button>
                </div>

                <!-- Laravel Authentication Form -->
                <form action="{{ route('login') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fa-solid fa-envelope text-xs"></i>
                            </span>
                            <input type="email" name="email" id="auth-email" value="admin@volunteerhub.ph" required 
                                   class="w-full border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs focus:ring-1 focus:ring-jci-blue focus:outline-none" placeholder="name@domain.com">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fa-solid fa-lock text-xs"></i>
                            </span>
                            <input type="password" name="password" id="auth-password" value="password" required 
                                   class="w-full border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs focus:ring-1 focus:ring-jci-blue focus:outline-none" placeholder="Enter security passphrase">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 space-y-3">
                        <button type="submit" class="w-full bg-jci-blue hover:bg-jci-dark text-white font-bold text-xs py-3 rounded-xl transition duration-300 flex items-center justify-center gap-2 shadow-lg shadow-blue-900/20">
                            <i class="fa-solid fa-right-to-bracket"></i> Secure Log In
                        </button>
                        <div class="text-center">
                            <span class="text-xs text-slate-400">Don't have an organization account? </span>
                            <a href="#" onclick="triggerToast('Organization registrations undergo compliance checks', 'info')" class="text-jci-blue hover:underline text-xs font-bold">Apply now</a>
                        </div>
                    </div>
                </form>

                <!-- Quick Credentials Panel -->
                <div class="mt-6 bg-slate-50 rounded-2xl p-4 border border-slate-200/60">
                    <h5 class="text-[10px] font-black uppercase text-slate-400 tracking-wider mb-2">Prototype Quick Fill:</h5>
                    <div class="flex flex-wrap gap-2">
                        <button onclick="selectAuthRole('admin')" class="bg-white border border-slate-200 hover:border-jci-blue px-2.5 py-1 rounded text-[10px] font-semibold text-slate-600 transition">Admin Node</button>
                        <button onclick="selectAuthRole('org')" class="bg-white border border-slate-200 hover:border-jci-blue px-2.5 py-1 rounded text-[10px] font-semibold text-slate-600 transition">JCI Surigao Wensies</button>
                        <button onclick="selectAuthRole('volunteer')" class="bg-white border border-slate-200 hover:border-jci-blue px-2.5 py-1 rounded text-[10px] font-semibold text-slate-600 transition">Volunteer (Juan)</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Auth JS to handle UI interaction -->
    <script>
        function selectAuthRole(role) {
            // Remove active classes from all buttons
            document.querySelectorAll('.auth-role-btn').forEach(btn => {
                btn.classList.remove('border-2', 'border-jci-blue', 'bg-blue-50/50', 'text-jci-blue');
                btn.classList.add('border-slate-200', 'text-slate-600');
            });
            
            // Add active classes to selected button
            const activeBtn = document.getElementById('authRole-' + role);
            if (activeBtn) {
                activeBtn.classList.remove('border-slate-200', 'text-slate-600');
                activeBtn.classList.add('border-2', 'border-jci-blue', 'bg-blue-50/50', 'text-jci-blue');
            }
            
            // Populate credentials
            quickFillCreds(role);
        }

        function quickFillCreds(role) {
            const emailInput = document.getElementById('auth-email');
            const passwordInput = document.getElementById('auth-password');
            
            const creds = {
                admin: { email: 'admin@volunteerhub.ph', pass: 'password' },
                org: { email: 'org@volunteerhub.ph', pass: 'password' },
                volunteer: { email: 'juan@volunteerhub.ph', pass: 'password' }
            };
            
            if (creds[role]) {
                emailInput.value = creds[role].email;
                passwordInput.value = creds[role].pass;
            }
        }
    </script>
</x-layout.app>
