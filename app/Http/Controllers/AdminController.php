<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\User;
use App\Event;
use App\Category;
use App\EventType;
use App\TicketGroup;
use App\Ticket;
use App\Collection;
use App\EventCollection;
use App\Journal;

class AdminController extends Controller {

	/**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display main dashboard.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $data['tasks'] = [
            [
                'name' => 'Design New Dashboard',
                'progress' => '87',
                'color' => 'danger'
            ],
        ];
        return view('admin/test')->with($data);
    }

    /**
     * Display a list of users.
     *
     * @param  Request  $request
     * @return Response
     */
    public function showUserList(Request $request)
    {
        return view('admin/user_index', [
            'page_title'    => 'User List',
            'users'     => User::all()
        ]);
    }

    /**
     * Display a list of events.
     *
     * @param  Request  $request
     * @return Response
     */
    public function showEventList(Request $request)
    {
        return view('admin/event_index', [
            'page_title'    => 'Event List',
            'events'        => Event::all(),
            'collections'   => Collection::all()
        ]);
    }

    /**
     * Display a form to create a new event.
     *
     * @param  Request  $request
     * @return Response
     */
    public function createEvent(Request $request)
    {
        return view('admin/event_create', [
            'page_title'    => 'Create Event',
            'categories'    => Category::all(),
            'event_types'   => EventType::all()
        ]);
    }

    /**
     * Display a form to edit an event.
     *
     * @param  Request  $request
     * @return Response
     */
    public function editEvent(Request $request, Event $event) 
    {
        return view('admin/event_edit', [
            'page_title'    => 'Edit Event',
            'event'         => $event,
            'categories'    => Category::all(),
            'event_types'   => EventType::all()
        ]);
    }

    /**
     * Create a new event.
     *
     * @param  Request  $request
     * @return Response
     */
    public function storeEvent(Request $request)
    {
        $started_at = substr($request->event_time, 0, 19);
        $ended_at   = substr($request->event_time, 22, 19);

        $event_id = $request->user()->events()->create([
            'category_id'   => $request->category,
            'event_type_id' => $request->event_type,
            'name'          => $request->name,
            'description'   => $request->description,
            'location'      => $request->location,
            'started_at'    => $started_at,
            'ended_at'      => $ended_at,
            'status'        => 0,
            'slug'          => str_slug($request->name, '-') . '-' . sprintf("%s", mt_rand(10000, 99999)),
        ])->id;

        for ($i = 1; $i <= $request->ticket_group_quantity; $i++) {
            $ticket_group_id = TicketGroup::create([
                'event_id'      => $event_id,
                'name'          => $request['ticket_name_'.$i],
                'price'         => $request['ticket_price_'.$i],
                'status'        => 1,
                'started_at'    => $started_at,
                'ended_at'      => $ended_at,
            ])->id;

            for ($j = 0; $j < $request['ticket_quantity_'.$i]; $j++) {
                Ticket::create([
                    'ticket_group_id' => $ticket_group_id,
                    'code'      => sprintf("%s", mt_rand(1000000, 9999999)),
                    'status'    => 1,
                ]);
            }
        }

        return redirect('admin/events');
    }

    /**
     * Edit an event.
     *
     * @param  Request  $request
     * @return Response
     */
    public function updateEvent(Request $request, Event $event)
    {
        $started_at = substr($request->event_time, 0, 19);
        $ended_at   = substr($request->event_time, 22, 19);

        $event->update([
            'category_id'   => $request->category,
            'event_type_id' => $request->event_type,
            'name'           => $request->name,
            'description'    => $request->description,
            'location'       => $request->location,
            'started_at'     => $started_at,
            'ended_at'       => $ended_at,
        ]);

        for ($i = 1; $i <= $request->ticket_group_quantity; $i++) {
            $ticket_group_id = TicketGroup::create([
                'event_id'      => $event->id,
                'name'          => $request['ticket_name_'.$i],
                'price'         => $request['ticket_price_'.$i],
                'status'        => 1,
                'started_at'    => $started_at,
                'ended_at'      => $ended_at,
            ])->id;

            for ($j = 0; $j < $request['ticket_quantity_'.$i]; $j++) {
                Ticket::create([
                    'ticket_group_id' => $ticket_group_id,
                    'code'      => sprintf("%s", mt_rand(1000000, 9999999)),
                    'status'    => 1,
                ]);
            }
        }

        return redirect('admin/events');
    }

    /**
     * Edit event's status.
     *
     * @param  Request  $request
     * @return Response
     */
    public function updateStatus(Request $request, Event $event)
    {
        $status = $request->status ? 1 : 0;

        $event->update([
            'status' => $status,
        ]);

        return redirect('admin/events');
    }

    /**
     * Display a list of collections.
     *
     * @param  Request  $request
     * @return Response
     */
    public function showCollectionList(Request $request) 
    {
        return view('admin/collection_index', [
            'page_title'    => 'Collection List',
            'collections'   => Collection::all()
        ]);
    }

    /**
     * Display a form to create a new collection.
     *
     * @param  Request  $request
     * @return Response
     */
    public function createCollection(Request $request)
    {
        return view('admin/collection_create', [
            'page_title'    => 'Create Collection',
        ]);
    }

    /**
     * Display a form to edit a collection.
     *
     * @param  Request  $request
     * @return Response
     */
    public function editCollection(Request $request, Collection $collection) 
    {
        return view('admin/collection_edit', [
            'page_title'    => 'Edit Collection',
            'collection'    => $collection
        ]);
    }

    /**
     * Create a new collection.
     *
     * @param  Request  $request
     * @return Response
     */
    public function storeEventCollection(Request $request)
    {
        EventCollection::create([
            'collection_id'      => $request->collection_id,
            'event_collection'  => $request->event_id,
        ]);

        return redirect('collections/'.$collection_slug);
    }

    /**
     * Create a new collection.
     *
     * @param  Request  $request
     * @return Response
     */
    public function storeCollection(Request $request)
    {
        Collection::create([
            'name'          => $request->name,
            'description'   => $request->description,
            'slug'          => str_slug($request->name, '-') . '-' . sprintf("%s", mt_rand(10000, 99999)),
        ]);

        return redirect('admin/collections');
    }

    /**
     * Edit an event.
     *
     * @param  Request  $request
     * @return Response
     */
    public function updateCollection(Request $request, Collection $collection)
    {
        $collection->update([
            'name'           => $request->name,
            'description'    => $request->description,
        ]);

        return redirect('admin/collections');
    }

    /**
     * Add an event to the collection.
     *
     * @param  Request  $request
     * @return Response
     */
    public function addEventCollection(Request $request)
    {
        $event_collection = EventCollection::where('collection_id', $request->collection_id)
            ->where('event_id', $request->event_id)
            ->get();

        if (count($event_collection) == 0) {
            EventCollection::create([
                'collection_id' => $request->collection_id,
                'event_id' => $request->event_id,
            ]);
        }

        return redirect('admin/collections');
    }

    /**
     * Display a list of journals.
     *
     * @param  Request  $request
     * @return Response
     */
    public function showJournalList(Request $request) 
    {
        return view('admin/journal_index', [
            'page_title'    => 'Journal List',
            'journals'      => Journal::all()
        ]);
    }

    /**
     * Display a form to create a new journal.
     *
     * @param  Request  $request
     * @return Response
     */
    public function createJournal(Request $request)
    {
        return view('admin/journal_create', [
            'page_title'    => 'Create Journal',
        ]);
    }

    /**
     * Display a form to edit an event.
     *
     * @param  Request  $request
     * @return Response
     */
    public function editJournal(Request $request, Journal $journal) 
    {
        return view('admin/Journal_edit', [
            'page_title'    => 'Edit Event',
            'journal'         => $journal,
        ]);
    }

}