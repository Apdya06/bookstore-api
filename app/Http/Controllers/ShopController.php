<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Provinces as ProvincesResourceCollection;
use App\Http\Resources\Cities as CityResourceCollection;
use App\Models;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    public function provinces()
    {
        return new ProvincesResourceCollection(Models\Province::get());
    }

    public function cities()
    {
        return new CityResourceCollection(Models\City::get());
    }
    public function shipping(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $status = 'error';
        $message = "";
        $data = null;
        $code = 200;
        if ($user) {
            $this->validate($request, [
                'name' => 'required',
                'address' => 'required',
                'phone' => 'required',
                'province_id' => 'required',
                'city_id' => 'required',
            ]);
            $user->name = $request->name;
            $user->address = $request->address;
            $user->phone = $request->phone;
            $user->province_id = $request->province_id;
            $user->city_id = $request->city_id;
            if ($user->save()) {
                $status = "success";
                $message = "Update Shipping Success";
                $data = $user;
            } else {
                $message = "Update Shipping Failed";
            }
        } else {
            $message = "User not found";
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $code);
    }
    public function couriers()
    {
        $couriers = [
            ['id' => 'jne', 'text' => 'JNE'],
            ['id' => 'tiki', 'text' => 'TIKI'],
            ['id' => 'pos', 'text' => 'POS'],
        ];
        return response()->json([
            'status' => 'success',
            'message' => 'couriers',
            'data' => $couriers
        ], 200);
    }
    protected function validateCart($carts)
    {
        $safe_carts = [];
        $total = [
            'quantity_before' => 0,
            'quantity' => 0,
            'price' => 0,
            'weight' => 0,
        ];
        $idx = 0;
        foreach ($carts as $cart) {
            $id = (int) $cart['id'];
            $quantity = (int) $cart['quantity'];
            $total['quantity_before'] += $quantity;
            $book = Models\Book::find($id);
            if ($book) {
                if ($book->stock > 0) {
                    $safe_carts[$idx]['id'] = $book->id;
                    $safe_carts[$idx]['title'] = $book->title;
                    $safe_carts[$idx]['cover'] = $book->cover;
                    $safe_carts[$idx]['price'] = $book->price;
                    $safe_carts[$idx]['weight'] = $book->weight;
                    if ($book->stock < $quantity) {
                        $quantity = (int) $book->stock;
                    }
                    $safe_carts[$idx]['quantity'] = $quantity;

                    $total['quantity'] += $quantity;
                    $total['price'] += $book->price * $quantity;
                    $total['weight'] += $book->weight * $quantity;
                    $idx++;
                } else {
                    continue;
                }
            }
        }
        return [
            'safe_carts' => $safe_carts,
            'total' => $total,
        ];
    }
    protected function getServices($data)
    {
        $url_cost = "https://api.rajaongkir.com/starter/cost";
        $key = "cdf84bfa4668577c95fa0d338d46d269";
        $postdata = http_build_query($data);
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url_cost,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postdata,
            CURLOPT_HTTPHEADER => [
                "content-type: application/x-www-form-urlencoded",
                "key: " . $key
            ],
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        return [
            'error' => $error,
            'response' => $response,
        ];
    }
    public function services(Request $request)
    {
        $status = "error";
        $message = "";
        $data = [];

        $this->validate($request, [
            'courier' => 'required',
            'carts' => 'required',
        ]);

        $user = Auth::user();
        if ($user) {
            $destination = $user->city_id;
            if ($destination > 0) {
                $origin = 153;
                $courier = $request->courier;
                $carts = $request->carts;
                $carts = json_decode($carts, true);
                $validCart = $this->validateCart($carts);
                $data['safe_carts'] = $validCart['safe_carts'];
                $data['total'] = $validCart['total'];
                $quantity_different = $data['total']['quantity_before'] <> $data['total']['quantity'];
                $weight = $validCart['total']['weight'] * 1000;
                if ($weight > 0) {
                    $parameter = [
                        "origin" => $origin,
                        "destination" => $destination,
                        "weight" => $weight,
                        "courier" => $courier,
                    ];
                    $respon_services = $this->getServices($parameter);
                    if ($respon_services['error'] == null) {
                        $services = [];
                        $response = json_decode($respon_services['response']);
                        if (isset($response->rajaongkir->results[0]->costs)) {
                            $costs = $response->rajaongkir->results[0]->costs;
                            foreach ($costs as $cost) {
                                $service_name = $cost->service;
                                $service_cost = $cost->cost[0]->value;
                                $service_estimation = str_replace('hari', '', trim($cost->cost[0]->etd));
                                $services[] = [
                                    'service' => $service_name,
                                    'cost' => $service_cost,
                                    'estimation' => $service_estimation,
                                    'resume' => $service_name . ' [ Rp. ' . number_format($service_cost) . ', Etd: '
                                        . $cost->cost[0]->etd . ' day(s) ]'
                                ];
                            }
                            if (count($services) > 0) {
                                $data['services'] = $services;
                                $status = "success";
                                $message = "Getting Services Success";
                            } else {
                                $message = "Courier Services Unavailable";
                            }
                            if ($quantity_different) {
                                $status = "Warning";
                                $message = "Check Cart Data, " . $message;
                            }
                        } else {
                            $message = "Invalid response from RajaOngkir API";
                        }
                    } else {
                        $message = "CURL Error #:" . $respon_services['error'];
                    }
                } else {
                    $message = "Weight Invalid";
                }
            } else {
                $message = "Destination Not Set";
            }
        } else {
            $message = "User Not Found";
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], 200);
    }

}
