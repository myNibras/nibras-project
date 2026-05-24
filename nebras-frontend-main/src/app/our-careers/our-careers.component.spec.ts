import { ComponentFixture, TestBed } from '@angular/core/testing';

import { OurCareersComponent } from './our-careers.component';

describe('OurCareersComponent', () => {
  let component: OurCareersComponent;
  let fixture: ComponentFixture<OurCareersComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [OurCareersComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(OurCareersComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
