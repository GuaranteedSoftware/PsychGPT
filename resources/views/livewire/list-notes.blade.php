<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="container px-6 mx-auto grid">
        <div class="w-full overflow-hidden py-8">
            <div class="w-full overflow-x-auto">
                <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs flex flex-col justify-between card">
                    <h3 class="mx-4 font-bold text-gray">
                        Notes
                    </h3>
                    <div class="w-full flex mt-4 fields-row">
                        <label class=" mx-8 flex w-full flex-row flex-nowrap gap-2 items-center">
                            <span class="text-gray-900 font-bold text-base">Search</span>
                            <input
                                wire:model.live="search"
                                class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field"
                                placeholder="Enter Patient ID"
                                type="text"
                            />
                        </label>
                    </div>
                    <table class="list-table">
                        <thead>
                            <tr>
                                <th scope="col">Patient ID</th>
                                <th scope="col">Start Date</th>
                                <th scope="col">End Date</th>
                                <th scope="col">Report by</th>
                                <th scope="col">Age</th>
                                <th scope="col">Gender</th>
                                <th scope="col">Status</th>
                                <th scope="col">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notes as $note)
                            <tr>
                                <td data-label="Patient ID">#{{$note->patient_id}}</td>
                                <td data-label="Start Date">{{$note->start_time}}</td>
                                <td data-label="End Date">{{$note->end_time}}</td>
                                <td data-label="Report by">{{$note->actor_type}}</td>
                                <td data-label="Age">{{$note->age}}</td>
                                <td data-label="Gender">{{$note->gender}}</td>
                                <td data-label="Status" class="cell-contents">
                                    <div class="cell-contents">
                                        <img src="/images/icons/color-note.svg">
                                        <span>Completed</span>
                                    </div>
                                </td>
                                <td data-label="View">
                                    <a href="{{route('update-note', $note->id)}}" class="cell-contents">
                                        <img src="/images/icons/view.svg">
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $notes->links('components.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
