<div class="container px-6 mx-auto grid">
    <!-- Main Page content -->
    <div class="w-full overflow-hidden pt-8">
        <div class="w-full overflow-x-auto card">
            <div class="steps-wrapper">
                <div class="step-container @if ($step == 1) active @endif">
                    <div class="step-icon">
                        <div class="step-number">
                            1
                        </div>
                    </div>
                    <h3 class="step-title">
                        Patient information
                    </h3>
                </div>
                <div class="step-connector">
                    <div class="connector">
                        <img src="/images/arrow-icon.svg" alt="arrow">
                    </div>
                </div>
                <div class="step-container @if ($step == 2) active @endif">
                    <div class="step-icon">
                        <div class="step-number">
                            2
                        </div>
                    </div>
                    <h3 class="step-title">
                        Treatment details
                    </h3>
                </div>
                <div class="step-connector">
                    <div class="connector">
                        <img src="/images/arrow-icon.svg" alt="arrow">
                    </div>
                </div>
                <div class="step-container @if ($step == 3) active @endif">
                    <div class="step-icon">
                        <div class="step-number">
                            3
                        </div>
                    </div>
                    <h3 class="step-title">
                        Note
                    </h3>
                </div>
            </div>
            <div class="grid gap-6 mt-4 md:grid-cols-2 ">
                <!-- Information -->
                <div class="min-w-0 bg-white rounded-lg shadow-xs flex flex-col justify-between">
                    @if ($step == 1)
                    <div id="step-01">
                        <div class="w-full flex mt-2 fields-row">
                            <div class="flex w-full flex-row flex-wrap fields-column">
                                <label class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Patient ID</span>
                                    <input wire:model="patientId"
                                           class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                           placeholder="Enter Patient ID"
                                           type="text"
                                    />
                                    @error('patientId')
                                    <span x-init="() => { var first = document.getElementsByClassName('validation-error').item(0); window.scroll({
                                                        top: first.getBoundingClientRect().top,
                                                        behavior: 'smooth',
                                                    })  }" class="validation-error text-sm text-red-600">
                                    {{ $message }}
                                </span>
                                    @enderror
                                </label>
                            </div>
                        </div>
                        <div class="w-full flex mt-2 fields-row">
                            <div class="flex fields-column date-container">
                                <span class="text-gray-900 font-bold text-base">Start Date/Time</span>
                                <div class="flex flex-row date-field">
                                    <label class="flex w-full flex-row flex-wrap">
                                        <input wire:model="startTime"
                                               class="block w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                               type="datetime-local"
                                               value="{{$startTime}}"
                                        />
                                        @error('startTime')
                                        <span x-init="() => { var first = document.getElementsByClassName('validation-error').item(0); window.scroll({
                                                        top: first.getBoundingClientRect().top,
                                                        behavior: 'smooth',
                                                    })  }" class="validation-error text-sm text-red-600">
                                        {{ $message }}
                                    </span>
                                        @enderror
                                    </label>
                                </div>
                            </div>
                            <div class="flex fields-column date-container">
                                <span class="text-gray-900 font-bold text-base">End Date/Time</span>
                                <div class="flex flex-row date-field">
                                    <label class="flex w-full flex-row flex-wrap">
                                        <input wire:model="endTime"
                                               class="block w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                               type="datetime-local"
                                               value="{{$endTime}}"
                                        />
                                        @error('endTime')
                                        <span x-init="() => { var first = document.getElementsByClassName('validation-error').item(0); window.scroll({
                                                        top: first.getBoundingClientRect().top,
                                                        behavior: 'smooth',
                                                    })  }" class="validation-error text-sm text-red-600">
                                        {{ $message }}
                                    </span>
                                        @enderror
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="w-full flex mt-2 fields-row">
                            <div class="flex fields-column">
                                <label class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Note Type</span>
                                    <select wire:model="noteType"
                                            class="w-full mt-1 text-sm form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field">
                                        <option value="" disabled>--Choose an option--</option>
                                        @foreach ($noteTypes as $noteType)
                                            <option value="{{$noteType}}"
                                                    wire:key="{{$noteType}}">{{$noteType}}</option>
                                        @endforeach
                                    </select>
                                    @error('noteType')
                                    <span x-init="() => { var first = document.getElementsByClassName('validation-error').item(0); window.scroll({
                                                        top: first.getBoundingClientRect().top,
                                                        behavior: 'smooth',
                                                    })  }" class="validation-error text-sm text-red-600">
                                        {{ $message }}
                                    </span>
                                    @enderror
                                </label>
                            </div>
                            <div class="flex fields-column">
                                <label class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Report by</span>
                                    <select wire:model="actorType"
                                            class="w-full mt-1 text-sm form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field">
                                        <option value="" disabled>--Choose an option--</option>
                                        @foreach ($actorTypes as $actorType)
                                            <option value="{{$actorType}}"
                                                    wire:key="{{$actorType}}">{{$actorType}}</option>
                                        @endforeach
                                    </select>
                                    @error('actorType')
                                    <span x-init="() => { var first = document.getElementsByClassName('validation-error').item(0); window.scroll({
                                                        top: first.getBoundingClientRect().top,
                                                        behavior: 'smooth',
                                                    })  }" class="validation-error text-sm text-red-600">
                                        {{ $message }}
                                    </span>
                                    @enderror
                                </label>
                            </div>
                        </div>
                        <div class="w-full flex mt-2 fields-row">
                            <div class="flex w-full flex-row flex-wrap fields-column">
                                <label class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Age</span>
                                    <input wire:model="age"
                                           class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                           placeholder="Enter age"
                                           type="number"
                                           min="1"
                                           max="150"
                                           step="1"
                                           pattern="[0-9]"
                                    />
                                    @error('age')
                                    <span x-init="() => { var first = document.getElementsByClassName('validation-error').item(0); window.scroll({
                                                        top: first.getBoundingClientRect().top,
                                                        behavior: 'smooth',
                                                    })  }" class="validation-error text-sm text-red-600">
                                        {{ $message }}
                                    </span>
                                    @enderror
                                </label>
                            </div>
                            <div class="flex fields-column">
                                <label class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Gender</span>
                                    <select wire:model="gender"
                                            class="w-full mt-1 text-sm form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field height-auto">
                                        <option value="" disabled>--Choose an option--</option>
                                        @foreach ($genders as $gender)
                                            <option value="{{$gender}}" wire:key="{{$gender}}">{{$gender}}</option>
                                        @endforeach
                                    </select>
                                    @error('gender')
                                    <span x-init="() => { var first = document.getElementsByClassName('validation-error').item(0); window.scroll({
                                                        top: first.getBoundingClientRect().top,
                                                        behavior: 'smooth',
                                                    })  }" class="validation-error text-sm text-red-600">
                                        {{ $message }}
                                    </span>
                                    @enderror
                                </label>
                            </div>
                        </div>
                        <div class="w-full flex mt-2 fields-row">
                            <div class="flex fields-column">
                                <input wire:model="clientDiagnosed"
                                       class="block mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-checkbox"
                                       type="checkbox"
                                />
                                <label class="block ml-4 mt-1 text-sm">
                                    Client Diagnosed
                                </label>
                            </div>
                            <div class="flex fields-column">
                                <input wire:model="diagnose" x-show="$wire.clientDiagnosed"
                                       class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                       placeholder="###-###"
                                       type="text"
                                />
                            </div>
                        </div>
                        <div class="w-full flex mt-4 button-container">
                            <button wire:click="incrementStep"
                               class="inline-flex items-center text-sm font-bold hover:text-black main-button">
                                Next
                            </button>
                        </div>
                    </div>
                    @elseif ($step == 2)
                    <div id="step-02" class="big-screen">
                        <div class="w-full flex mt-2 fields-row">
                            <div class="flex fields-column">
                                <label title="i.e. Improving coping skills" class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Goals</span>
                                    <textarea wire:model="goals"
                                              class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                              placeholder="Type here..."
                                    ></textarea>
                                    @error('goals')
                                    <span x-init="() => { var first = document.getElementsByClassName('validation-error').item(0); window.scroll({
                                                        top: first.getBoundingClientRect().top,
                                                        behavior: 'smooth',
                                                    })  }" class="validation-error text-sm text-red-600">
                                        {{ $message }}
                                    </span>
                                    @enderror
                                </label>
                            </div>
                            <div class="flex fields-column">
                                <label title="i.e. Brother left for army, dog died" class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Comments</span>
                                    <textarea  wire:model="comments" x-bind:disabled="!$wire.goals"
                                               class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                               placeholder="Type here..."
                                    ></textarea>
                                </label>
                            </div>
                        </div>
                        <div class="w-full flex mt-2 fields-row">
                            <div class="flex fields-column">
                                <label title="i.e. Specific things you would like them to work on" class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Recommendations</span>
                                    <textarea wire:model="recommendations" x-bind:disabled="!$wire.goals"
                                              class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                              placeholder="Type here..."
                                    ></textarea>
                                </label>
                            </div>
                            <div x-show="$wire.noteType!='CPST'" class="flex fields-column">
                                <label title="i.e. Struggling in school, Crying about everything" class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Behavior</span>
                                    <textarea wire:model="behavior" x-bind:disabled="!$wire.goals"
                                              class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                              placeholder="Type here..."
                                    ></textarea>
                                </label>
                            </div>
                        </div>
                        <div class="w-full flex mt-2 fields-row">
                            <div class="flex fields-column">
                                <label title="i.e. Feelings wheel, Coping strategies" class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Intervention</span>
                                    <textarea wire:model="intervention" x-bind:disabled="!$wire.goals"
                                              class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                              placeholder="Type here..."
                                    ></textarea>
                                </label>
                            </div>
                            <div x-show="$wire.noteType!='CPST'" class="flex fields-column">
                                <label title="i.e. Quiet, Tearful" class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Client Response</span>
                                    <textarea wire:model="clientResponse" x-bind:disabled="!$wire.goals"
                                              class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                              placeholder="Type here..."
                                    ></textarea>
                                </label>
                            </div>
                        </div>
                        <div class="w-full flex mt-2 fields-row">
                            <div x-show="$wire.noteType!='CPST'" class="flex fields-column">
                                <label title="i.e. Daily emotion tracking in journal" class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Homework Given</span>
                                    <textarea wire:model="homeworkGiven" x-bind:disabled="!$wire.goals"
                                              class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                              placeholder="Type here..."
                                    ></textarea>
                                </label>
                            </div>
                        </div>
                        <div class="w-full flex mt-4 button-container">
                            <button wire:click="decrementStep" type="button" class="inline-flex items-center text-sm font-bold  secondary-button">
                                Back
                            </button>
                            <button wire:click="generateNote" class="inline-flex items-center text-sm font-bold hover:text-white main-button">
                                Generate Note
                            </button>
                        </div>
                    </div>
                    <div id="step-02-01" class="small-screen">
                        <div class="w-full flex mt-2 fields-row">
                            <div class="flex fields-column">
                                <label title="i.e. Improving coping skills" class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Goals</span>
                                    <textarea wire:model="goals"
                                              class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                              placeholder="Type here..."
                                    ></textarea>
                                    @error('goals')
                                    <span x-init="() => { var first = document.getElementsByClassName('validation-error').item(0); window.scroll({
                                                        top: first.getBoundingClientRect().top,
                                                        behavior: 'smooth',
                                                    })  }" class="validation-error text-sm text-red-600">
                                        {{ $message }}
                                    </span>
                                    @enderror
                                </label>
                            </div>
                            <div class="flex fields-column ">
                                <label title="i.e. Brother left for army, dog died" class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Comments</span>
                                    <textarea  wire:model="comments" x-bind:disabled="!$wire.goals"
                                               class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                               placeholder="Type here..."
                                    ></textarea>
                                </label>
                            </div>
                        </div>
                        <div class="w-full flex mt-2 fields-row">
                            <div class="flex fields-column ">
                                <label title="i.e. Specific things you would like them to work on" class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Recommendations</span>
                                    <textarea wire:model="recommendations" x-bind:disabled="!$wire.goals"
                                              class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                              placeholder="Type here..."
                                    ></textarea>
                                </label>
                            </div>
                            <div x-show="$wire.noteType!='CPST'" class="flex fields-column full">
                                <label title="i.e. Struggling in school, Crying about everything" class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Behavior</span>
                                    <textarea wire:model="behavior" x-bind:disabled="!$wire.goals"
                                              class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                              placeholder="Type here..."
                                    ></textarea>
                                </label>
                            </div>
                        </div>
                        <div class="w-full flex mt-4 button-container">
                            <button wire:click="decrementStep" type="button" class="inline-flex items-center text-sm font-bold  secondary-button">
                                Back
                            </button>
                            <button x-bind:disabled="!$wire.goals" wire:click="generateNote" class="inline-flex items-center text-sm font-bold hover:text-white main-button">
                                Next
                            </button>
                        </div>
                    </div>
                    <div id="step-02-02" class="small-screen">
                        <div class="w-full flex mt-2 fields-row">
                            <div class="flex fields-column ">
                                <label title="i.e. Feelings wheel, Coping strategies"
                                       class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Intervention</span>
                                    <textarea wire:model="intervention" x-bind:disabled="!$wire.goals"
                                              class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                              placeholder="Type here..."
                                    ></textarea>
                                </label>
                            </div>
                            <div x-show="$wire.noteType!='CPST'" class="flex fields-column">
                                <label title="i.e. Quiet, Tearful" class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Client Response</span>
                                    <textarea wire:model="clientResponse" x-bind:disabled="!$wire.goals"
                                              class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                              placeholder="Type here..."
                                    ></textarea>
                                </label>
                            </div>
                        </div>
                        <div class="w-full flex mt-2 fields-row">
                            <div x-show="$wire.noteType!='CPST'" class="flex fields-column">
                                <label title="i.e. Daily emotion tracking in journal"
                                       class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Homework Given</span>
                                    <textarea wire:model="homeworkGiven" x-bind:disabled="!$wire.goals"
                                              class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                              placeholder="Type here..."
                                    ></textarea>
                                </label>
                            </div>
                        </div>
                        <div class="w-full flex mt-4 button-container">
                            <button wire:click="decrementStep" type="button" class="inline-flex items-center text-sm font-bold  secondary-button">
                                Back
                            </button>
                            <button x-bind:disabled="!$wire.goals" wire:click="generateNote" class="inline-flex items-center text-sm font-bold hover:text-white main-button">
                                Generate Note
                            </button>
                        </div>
                    </div>
                    @elseif ($step == 3)
                    <div id="step-03">
                        <div class="w-full flex mt-2 fields-row">
                            <div class="flex fields-column">
                                <label title="Additional Note Refinements" class="flex w-full flex-row flex-wrap">
                                    <span class="text-gray-900 font-bold text-base">Genie</span>
                                    <textarea wire:model="genie" x-bind:disabled="!$wire.generatedNote" rows="20"
                                              class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                              placeholder="Type here..."
                                    ></textarea>
                                </label>
                            </div>
                        </div>
                        <div class="w-full flex mt-4 button-container">
                            <button wire:click="decrementStep" type="button" class="inline-flex items-center text-sm font-bold  secondary-button">
                                Back
                            </button>
                            <button wire:click="generateNote" x-bind:disabled="!$wire.genie" class="inline-flex items-center text-sm font-bold hover:text-white main-button">
                                Update Note
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
                <!-- Note -->
                <div id="note" class="min-w-0 bg-white rounded-lg shadow-xs dark:bg-gray-800 flex flex-col">
                    <h3 class="text-gray-900 font-bold text-base">
                        Note
                    </h3>
                    <textarea wire:stream="generatedNote" rows="20" disabled
                              class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                    >{{ $generatedNote }}</textarea>
                    @if($generatedNote)
                    <div class="w-full flex mt-4 button-container">
                        <button wire:click="saveNote" href="" class="inline-flex items-center text-sm font-bold hover:text-white main-button">
                            Save
                        </button>
                        {{--                        <button href="" class="inline-flex items-center text-sm font-bold hover:text-black secondary-button">--}}
                        {{--                            <img src="/images/icons/print-icon.svg" alt="Print">--}}
                        {{--                            <span class="ml-4">Print</span>--}}
                        {{--                        </button>--}}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
