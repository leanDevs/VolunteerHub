<x-layout.org>

    <!-- Left Column: Core Workspaces -->
    <div class="lg:col-span-8 space-y-8">
        <!-- Org Summary metrics cards -->
        <div class="bg-gradient-to-r from-jci-blue to-jci-dark text-white rounded-2xl p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h3 class="text-xl font-bold">Wensie Management Console</h3>
                <p class="text-xs text-slate-200 mt-1 max-w-xl">Empowering Surigaonon women through dynamic leadership, community projects, and responsive socioeconomic outreach programs.</p>
            </div>
            <div class="flex gap-4">
                <div class="text-center bg-white/10 p-3 rounded-xl border border-white/10">
                    <div class="text-xl font-extrabold text-jci-accent" id="org-metric-events">
                        {{ $myEventsCount }}
                    </div>
                    <div class="text-[10px] uppercase text-slate-300">My Events</div>
                </div>
                <div class="text-center bg-white/10 p-3 rounded-xl border border-white/10">
                    <div class="text-xl font-extrabold text-jci-accent">
                        {{ $assignedVolunteersCount }}
                    </div>
                    <div class="text-[10px] uppercase text-slate-300">Assigned Vols</div>
                </div>
            </div>
        </div>

        <!-- Dynamic Planner Widget -->
        <div class="glass-card premium-shadow rounded-3xl p-6 space-y-4 animate-fade-in-up">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm md:text-base">
                    <i class="fa-solid fa-calendar-day text-jci-blue"></i> Event Planner & Tasks
                </h3>
                <button onclick="document.getElementById('modal-new-event').classList.remove('hidden'); addTaskRow();" class="bg-jci-blue hover:bg-jci-dark text-white text-xs px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1">
                    <i class="fa-solid fa-plus"></i> Launch New Event
                </button>
            </div>
            
            <div class="space-y-6" id="org-events-container">
                @if($events->count() > 0)
                    @foreach($events as $event)
                        <div class="p-6 bg-slate-50/20 rounded-2xl border border-slate-200/80 space-y-4 hover:border-slate-300 transition-all duration-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="bg-blue-100 text-jci-blue text-[9px] font-black uppercase px-2 py-0.5 rounded">
                                            {{ $event->location }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-bold">
                                            {{ $event->start_time->format('M d, Y h:i A') }}
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-base text-slate-900 mt-1">{{ $event->title }}</h4>
                                    <p class="text-xs text-slate-500 mt-1">{{ $event->description }}</p>
                                </div>
                                <span class="bg-emerald-500 text-white text-[9px] font-black uppercase px-2 py-0.5 rounded">
                                    {{ $event->status }}
                                </span>
                            </div>

                            <!-- Tasks Grid -->
                            <div class="border-t border-slate-100 pt-4 space-y-2">
                                <h5 class="text-xs font-bold text-slate-700">Checklist & Action items</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($event->tasks as $task)
                                        <div class="p-3 bg-white border border-slate-200/60 rounded-2xl shadow-sm flex justify-between items-center hover-lift">
                                            <div>
                                                <h6 class="text-xs font-bold text-slate-800">{{ $task->title }}</h6>
                                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                                    @if($task->priority === 'high')
                                                        <span class="bg-rose-100 text-rose-700 text-[8px] font-bold px-1.5 py-0.5 rounded">High Priority</span>
                                                    @elseif($task->priority === 'medium')
                                                        <span class="bg-amber-100 text-amber-700 text-[8px] font-bold px-1.5 py-0.5 rounded">Medium Priority</span>
                                                    @else
                                                        <span class="bg-slate-100 text-slate-600 text-[8px] font-bold px-1.5 py-0.5 rounded">Low Priority</span>
                                                    @endif

                                                    @if($task->status === 'completed')
                                                        <span class="bg-emerald-100 text-emerald-700 text-[8px] font-bold px-1.5 py-0.5 rounded">Completed</span>
                                                    @else
                                                        <span class="bg-blue-50 text-jci-blue text-[8px] font-bold px-1.5 py-0.5 rounded">Pending</span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- List assignments -->
                                            <div class="text-right">
                                                @php
                                                    $assignment = $event->assignments->where('task_id', $task->id)->first();
                                                @endphp
                                                @if($assignment)
                                                    <span class="text-[9px] font-bold text-slate-400 block">Assigned:</span>
                                                    <span class="text-[10px] font-black text-jci-blue">{{ $assignment->user->name }}</span>
                                                @else
                                                    <span class="text-[9px] italic text-slate-400">Unassigned</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <i class="fa-solid fa-calendar-xmark text-slate-300 text-4xl mb-2"></i>
                        <p class="text-xs text-slate-400 font-medium">No community events launched yet. Click Launch New Event to start.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recordkeeping Module for compliance -->
        <div class="glass-card premium-shadow rounded-3xl p-6 space-y-4 animate-fade-in-up" style="animation-delay: 100ms;">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-folder-open text-jci-blue"></i> Persistent Recordkeeping Module
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Retrievable multi-year compliance archiving for regional audits.</p>
                </div>
                
                <!-- Real-Simulated File Upload Form -->
                <form action="{{ route('org.documents.store') }}" method="POST" enctype="multipart/form-data" id="upload-doc-form">
                    @csrf
                    <input type="file" name="document_file" id="simulated-file-input" class="hidden" onchange="document.getElementById('upload-doc-form').submit()">
                    <button type="button" onclick="document.getElementById('simulated-file-input').click()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload Document
                    </button>
                </form>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="archive-files-grid">
                @if($complianceDocuments->count() > 0)
                    @foreach($complianceDocuments as $doc)
                        <div class="p-4 bg-gradient-to-br from-white to-slate-50/50 border border-slate-200/80 rounded-2xl flex flex-col justify-between hover-lift premium-shadow">
                            <div class="flex items-start gap-3">
                                <i class="fa-solid fa-file-pdf text-red-500 text-2xl pt-1"></i>
                                <div>
                                    <h5 class="text-xs font-bold text-slate-800 break-words leading-tight">
                                        {{ $doc->original_data['filename'] }}
                                    </h5>
                                    <p class="text-[9px] text-slate-400 mt-0.5">
                                        {{ $doc->original_data['size'] }} | {{ $doc->original_data['uploaded_at'] }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 border-t border-slate-200/60 pt-2 flex justify-between items-center">
                                <span class="text-[8px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full uppercase">Archived</span>
                                <button onclick="triggerToast('Retrieving compliance file block from database...', 'info')" class="text-jci-blue hover:underline text-[9px] font-bold">
                                    Verify Hash
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-span-full p-6 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <p class="text-xs text-slate-400">No compliance documents uploaded yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: AI Skill-Matching Module -->
    <div class="lg:col-span-4 shrink-0">
        <div class="glass-card premium-shadow rounded-3xl p-6 flex flex-col max-h-[calc(100vh-120px)] overflow-hidden space-y-4 animate-fade-in-up" style="animation-delay: 150ms;">
            <div class="pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2 text-jci-blue mb-1">
                    <i class="fa-solid fa-brain text-xl"></i>
                    <span class="bg-blue-100 text-blue-800 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full">Skill Engine</span>
                </div>
                <h3 class="font-bold text-slate-800">Dynamic Skill Matching</h3>
                <p class="text-xs text-slate-400 mt-0.5">Find best-fit volunteers based on registered capacities, profile listings, and past engagement rates.</p>
            </div>
            
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-2">1. Select Target Task Requirements</label>
                <select id="skillmatch-task-select" onchange="runLiveSkillMatching()" class="w-full border border-slate-200 rounded-lg p-2.5 text-xs font-semibold focus:ring-1 focus:ring-jci-blue focus:outline-none mb-3 bg-white">
                    @if($allOrgTasks->count() > 0)
                        @foreach($allOrgTasks as $t)
                            <option value="{{ $t->id }}" {{ $t->id == $selectedTaskId ? 'selected' : '' }}>
                                {{ $t->title }} ({{ $t->event->title }})
                            </option>
                        @endforeach
                    @else
                        <option value="">No active tasks available</option>
                    @endif
                </select>
                
                <div class="flex flex-wrap gap-1" id="skillmatch-required-skills-badges">
                    @if($selectedTask && $selectedTask->skills->count() > 0)
                        @foreach($selectedTask->skills as $sk)
                            <span class="bg-blue-100 text-jci-blue text-[9px] font-bold px-2 py-0.5 rounded">
                                <i class="fa-solid fa-tag"></i> {{ $sk->name }}
                            </span>
                        @endforeach
                    @else
                        <span class="text-[10px] text-slate-400 italic">No skills required for this task.</span>
                    @endif
                </div>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">2. Match Rankings</span>
                <span class="text-[10px] text-slate-400">Recalculates dynamically</span>
            </div>
            
            <div class="flex-grow overflow-y-auto custom-scrollbar space-y-3 pr-1" id="skillmatch-results-list">
                @if(count($skillMatches) > 0)
                    @foreach($skillMatches as $match)
                        <div class="p-3 bg-slate-50/50 rounded-2xl border border-slate-200/80 flex items-center justify-between gap-3 hover-lift">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-jci-blue text-white flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($match['volunteer']->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h5 class="text-xs font-bold text-slate-800">{{ $match['volunteer']->name }}</h5>
                                    
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($match['matched_skills'] as $msk)
                                            <span class="bg-emerald-100 text-emerald-800 text-[8px] font-bold px-1 py-0.5 rounded">
                                                {{ $msk->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                @php
                                    $scoreColor = $match['score'] >= 80 ? 'bg-emerald-500 shadow-emerald-500/20' : ($match['score'] >= 50 ? 'bg-amber-500 shadow-amber-500/20' : 'bg-slate-400 shadow-slate-400/20');
                                @endphp
                                <span class="{{ $scoreColor }} text-white text-[9px] font-black px-2.5 py-1.5 rounded-xl shadow-md">
                                    {{ $match['score'] }}%
                                </span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center p-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <p class="text-xs text-slate-400">Select a task that requires skills to see matches.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- NEW EVENT MODAL -->
    <x-ui.modal id="modal-new-event" title="Launch New Community Action">
        <form action="{{ route('org.events.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Event Title</label>
                <input name="title" type="text" required placeholder="e.g., Clean Beach Reschedule" class="w-full border border-slate-200 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-jci-blue focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Location Address</label>
                <input name="location" type="text" required placeholder="e.g., Surigao Boulevard" class="w-full border border-slate-200 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-jci-blue focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Start Date & Time</label>
                    <input name="start_time" type="datetime-local" required class="w-full border border-slate-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-jci-blue focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">End Date & Time</label>
                    <input name="end_time" type="datetime-local" required class="w-full border border-slate-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-jci-blue focus:outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Max Capacity (Vols)</label>
                    <input name="capacity" type="number" min="1" placeholder="e.g. 50" class="w-full border border-slate-200 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-jci-blue focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Description</label>
                <textarea name="description" required placeholder="Describe details of the outreach event..." class="w-full border border-slate-200 rounded-lg p-2.5 text-xs h-20 focus:ring-1 focus:ring-jci-blue focus:outline-none resize-none"></textarea>
            </div>

            <!-- Tasks Builder Section inside Modal -->
            <div class="space-y-3 pt-3 border-t border-slate-100">
                <div class="flex justify-between items-center">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Initial Checklist Items</label>
                    <button type="button" onclick="addTaskRow()" class="text-jci-blue hover:text-jci-dark text-xs font-bold flex items-center gap-0.5">
                        <i class="fa-solid fa-plus text-[10px]"></i> Add Task Row
                    </button>
                </div>
                
                <div class="space-y-3 max-h-[160px] overflow-y-auto custom-scrollbar pr-1" id="modal-tasks-container">
                    <!-- Dynamic task rows get injected here -->
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-new-event').classList.add('hidden')" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs px-4 py-2.5 rounded-xl transition duration-200">
                    Cancel
                </button>
                <button type="submit" class="bg-jci-blue hover:bg-jci-dark text-white font-bold text-xs px-4 py-2.5 rounded-xl transition duration-200 shadow-md">
                    Launch Event
                </button>
            </div>
        </form>
    </x-ui.modal>

    <!-- Org Dashboard specific script -->
    <script>
        function runLiveSkillMatching() {
            const select = document.getElementById('skillmatch-task-select');
            const taskId = select.value;
            if (taskId) {
                window.location.href = "{{ route('org.dashboard') }}?task_id=" + taskId;
            }
        }

        let taskIndex = 0;
        function addTaskRow() {
            const container = document.getElementById('modal-tasks-container');
            const row = document.createElement('div');
            row.className = 'p-3 bg-slate-50 rounded-xl border border-slate-200/60 space-y-2 relative';
            row.id = 'task-row-' + taskIndex;
            row.innerHTML = `
                <button type="button" onclick="this.parentElement.remove()" class="absolute top-2 right-2 text-slate-400 hover:text-rose-600"><i class="fa-solid fa-xmark"></i></button>
                <div>
                    <label class="block text-[9px] font-bold uppercase text-slate-400 mb-1">Task Title</label>
                    <input name="task_title[${taskIndex}]" type="text" required placeholder="e.g., Set up registration booth" class="w-full border border-slate-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-jci-blue focus:outline-none bg-white">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[9px] font-bold uppercase text-slate-400 mb-1">Priority</label>
                        <select name="task_priority[${taskIndex}]" class="w-full border border-slate-200 rounded-lg p-2 text-xs focus:outline-none bg-white">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold uppercase text-slate-400 mb-1">Required Skill</label>
                        <select name="task_skills[${taskIndex}][]" class="w-full border border-slate-200 rounded-lg p-2 text-xs focus:outline-none bg-white">
                            @foreach($skills as $skill)
                                <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            `;
            container.appendChild(row);
            taskIndex++;
        }
    </script>

</x-layout.org>
